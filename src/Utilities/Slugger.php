<?php
namespace PsyNexus\Utilities;
class Slugger
{
    public function createSlug(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
        $text = strtolower(trim($text));
        $text = preg_replace('/[\s-]+/', '-', $text);
        return $text;
    }
}
