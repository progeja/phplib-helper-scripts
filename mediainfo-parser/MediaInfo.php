<?php
/**
 * Project skriptid.
 * File: MediaInfo.php
 * User: roomet
 * Date: 23.08.2018 13:25
 */

class MediaInfo implements Iterator, ArrayAccess, Countable
{
    private $fileName = '';

    private $lineNr = 0;

    private $fileData = [];

    private $itemsCount = 0;

    /**
     * @var SplFileObject
     */
    private $file = null;


    public function __construct($fileName)
    {
        $this->lineNr   = 0;
        $this->fileName = $fileName;

    }

    private function load()
    {
        if (!is_readable($this->fileName)) {
            throw new Exception("Fail '{$this->fileName}' ei ole loetav" . NL);
        }
        $this->file = new SplFileObject($this->fileName, 'r');
        $this->file->seek(PHP_INT_MAX);
        $this->itemsCount = $this->file->key() + 1;
        $this->file->rewind();
    }

    private function parseLine(string $line)
    {
        // Kino\\Komplektid\Fast & Furious|2003 - 2 Fast 2 Furious [FHD]|Matroska|01:47:35.862|6809592|5495223144|x|1|AVC|1|DTS|24|23.976!!V:|0|(2003) 2 Fast 2 Furious 1080p multisub [mkvonly]|1920x816|AVC|V_MPEG4/ISO/AVC|video/H264!!Audio-0:en-English(DTS)!!Sub-0:en-English/Sub-1:ar-Arabic/Sub-2:bg-Bulgarian/Sub-3:hr-Croatian/Sub-4:cs-Czech/Sub-5:da-Danish/Sub-6:nl-Dutch/Sub-7:fi-Finnish/Sub-8:fr-French/Sub-9:de-German/Sub-10:el-Greek/Sub-11:he-Hebrew/Sub-12:hu-Hungarian/Sub-13:is-Icelandic/Sub-14:id-Indonesian/Sub-15:it-Italian/Sub-16:no-Norwegian/Sub-17:pl-Polish/Sub-18:pt-Portuguese/Sub-19:ro-Romanian/Sub-20:es-Spanish/Sub-21:sv-Swedish/Sub-22:th-Thai/Sub-23:tr-Turkish/
        // FAILIKAUST|FAILINIMI|FORMAAT|PIKKUS|
    }

    public function current()
    {
        return $this->file->current();
    }

    public function next()
    {
        $this->file->next();
    }

    public function key()
    {
        return $this->file->key();
    }

    public function valid()
    {
        return $this->file->valid();
    }

    public function rewind()
    {
        $this->file->current();
    }

    public function offsetExists($offset)
    {
        return isset($this->fileData[$this->lineNr]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->fileData[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->fileData[] = $value;
        } else {
            $this->fileData[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->fileData[$offset]);
        }
    }

    public function count()
    {
        return $this->itemsCount;
    }
}