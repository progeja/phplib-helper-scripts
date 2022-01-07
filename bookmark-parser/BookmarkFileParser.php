<?php

namespace Bookworm\Helper;

use Bookworm\Exception\FileNotFoundException;
use Bookworm\Exception\InvalidBookmarkFileException;
use JetBrains\PhpStorm\ArrayShape;

class BookmarkFileParser
{
    private const LINE_META   = 1;
    private const LINE_TITLE  = 2;
    private const LINE_H1     = 3;
    private const LINE_H3     = 4;
    private const LINE_A      = 5;
    private const LINE_DL_END = 6;
    private const LINE_OTHER  = 99;

    public function __construct()
    {
    }

    /**
     * @throws \Bookworm\Exception\FileNotFoundException|\Bookworm\Exception\InvalidBookmarkFileException
     */
    public function parseBMFile(string $bookmarkFileName): array
    {
        if (!is_readable($bookmarkFileName)) {
            throw new FileNotFoundException("Can not read file '{$bookmarkFileName}'!");
        }

        return $this->parseBMString(file_get_contents($bookmarkFileName));
    }

    /**
     * @throws \Bookworm\Exception\InvalidBookmarkFileException
     */
    public function parseBMString(string $bookmarkContent): array
    {
        $levels       = 0;
        $parent_stack = [];

        if (!$this->isBookmarks($bookmarkContent)) {
            throw new InvalidBookmarkFileException('This is not valid bookmark file!');
        }

        $content = $this->prepareContent($bookmarkContent);
        $result  = [];

        $idx = 1;
        foreach ($content as $lineContent) {
            if (trim($lineContent) === '') { // ignore empty lines
                continue;
            }
            $type = $this->getLineType($lineContent);
            if ($type === self::LINE_H3) {
                $levels++;
                array_unshift($parent_stack, $idx);
            }
            if ($type === self::LINE_DL_END) {
                $levels = max(0, $levels - 1);
                array_shift($parent_stack);
            }

            //echo "$lineNo :: [$levels] $lineContent\n";
            if (in_array($type, [self::LINE_H3, self::LINE_A], true)) {
                $data           = $this->parseLine($lineContent);
                $data['id']     = $idx;
                $data['parent'] = $parent_stack[1] ?? 0;
                $data['level']  = $levels;
                $result[$idx]   = $data;
                $idx++;
            }
        }

        return $result;
    }

    /**
     * @param string $content
     *
     * @return string[]
     */
    private function prepareContent(string $content): array
    {
        $content = $this->clearComments($content);

        return array_map(
            'trim',
            explode("\n", $content)
        );
    }

    /**
     * @param string $content String to clean up
     *
     * @return string
     */
    private function clearComments(string $content): string
    {
        return preg_replace('|<!--.*?-->|s', '', $content);
    }

    private function isBookmarks(string $content): bool
    {
        $content = trim($content);

        return str_starts_with($content, '<!DOCTYPE NETSCAPE-Bookmark-file-1>');
    }

    /**
     * @param string $lineContent
     *
     * @return int
     */
    private function getLineType(string $lineContent): int
    {
        if (str_starts_with($lineContent, '<META')) {
            return self::LINE_META;
        }
        if (str_starts_with($lineContent, '<TITLE')) {
            return self::LINE_TITLE;
        }
        if (str_starts_with($lineContent, '<H1')) {
            return self::LINE_H1;
        }
        if (str_starts_with($lineContent, '<DT><H3')) {
            return self::LINE_H3;
        }
        if (str_starts_with($lineContent, '<DT><A')) {
            return self::LINE_A;
        }
        if (str_starts_with($lineContent, '</DL>')) {
            return self::LINE_DL_END;
        }

        return self::LINE_OTHER;
    }

    /**
     * @param string $lineContent
     *
     * @return array
     */
    #[ArrayShape(['caption' => "string", 'parameter' => "array"])] private function parseLine(string $lineContent): array
    {
        preg_match('%<([a-z0-9]+) ([^>]+)>([^<]*)</%im', $lineContent, $mm1);

        $params     = explode(' ', $mm1[2]);
        $caption    = $mm1[3];
        $parameters = [];

        foreach ($params as $param) {
            $set = explode('=', $param);
            if (in_array(strtolower($set[0]), ['add_date', 'last_modified', 'last_visit'])) {
                $timestamp           = trim($set[1], '"');
                $timeDate            = date('Y-m-d H:i:s', $timestamp);
                $parameters[$set[0]] = [
                    'timestamp' => $timestamp,
                    'datetime'  => $timeDate,
                ];
            } else {
                $parameters[$set[0]] = trim($set[1], '"');
            }
        }

        return [
            'caption'   => $caption,
            'parameter' => $parameters,
        ];
    }
}
