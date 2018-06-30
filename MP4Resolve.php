<?php
/**
 * User: w
 * Date: 2018/7/1/001
 * Time: 1:21
 */

namespace VideoResolve;


use Exception;

class MP4Resolve
{

    const TYPE_INT = 0;
    const TYPE_STR = 1;

    private static function byteToInt($buf)
    {
        $n = 0;
        for ($i = 0; $i < strlen($buf); $i++) {
            $n *= 0x100;
            $n += ord($buf[$i]);
        }
        return $n;
    }

    private $fp;

    public function __construct($file)
    {
        $this->fp = fopen($file, 'r');
        if (!$this->fp) {
            throw new Exception("No file found!");
        }
        $this->initFtyp();
        $this->initMvhd();
    }

    private $ftyp_box_data;

    private static $FTYP_STRUCT = [
        "box_len" => [0, 4],
        "structure_id" => [1, 4],
        "major_brand" => [1, 4],
        "minor_version" => [0, 4],
    ];

    private function initFtyp()
    {
        $f = $this->fp;

        $result = [];
        foreach (self::$FTYP_STRUCT as $index => $item) {
            $data = fread($f, $item[1]);
            switch ($item[0]) {
                case self::TYPE_INT:
                    $result[$index] = self::byteToInt($data);
                    break;
                case self::TYPE_STR:
                    $result[$index] = $data;
                    break;
            }
        }

        if (ctype_lower($result['structure_id']) != "ftyp") {
            throw new Exception("Can't find ftyp structure");
        }

        $result['compatible_brands'] = fread($f, $result['box_len'] - 16);
        $this->ftyp_box_data = $result;
        return $this->ftyp_box_data;
    }

    private static $MVHD_STRUCT = [
        "box_len" => [0, 4],
        "structure_id" => [1, 4],
        "version" => [0, 1],
        "flags" => [0, 3],
        "creation_time" => [0, 4],
        "change_time" => [0, 4],
        "timescale" => [0, 4],
        "duration" => [0, 4],
        "media_speed" => [0, 4],
        "media_volume" => [0, 2],
        "reserved" => [0, 10],
        "matrix_structure" => [1, 36],
        "preview_time" => [0, 4],
        "preview_duration" => [0, 4],
        "poster_time" => [0, 4],
        "selection_time" => [0, 4],
        "selection_duration" => [0, 4],
        "current_time" => [0, 4],
        "next_track_id" => [0, 4],
    ];

    private $mvhd_box_data;

    private function passOfMvhd()
    {
        $f = $this->fp;
        fseek($f, 8, SEEK_CUR);
    }

    private function initMvhd()
    {
        $this->passOfMvhd();
        $f = $this->fp;
        $result = [];
        foreach (self::$MVHD_STRUCT as $index => $item) {
            $data = fread($f, $item[1]);
            switch ($item[0]) {
                case self::TYPE_INT:
                    $result[$index] = self::byteToInt($data);
                    break;
                case self::TYPE_STR:
                    $result[$index] = $data;
                    break;
            }
        }

        if (ctype_lower($result['structure_id']) != "mvhd") {
            throw new Exception("Can't find mvhd structure");
        }
        $result['play_time'] = $result['duration'] / $result['timescale'];
        $this->mvhd_box_data = $result;
        return $result;
    }

    public function getExt()
    {
        $result = ["MVHD" => $this->mvhd_box_data, "FTYP" => $this->ftyp_box_data];
        return $result;
    }


}