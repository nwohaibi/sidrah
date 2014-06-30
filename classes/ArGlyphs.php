<?php
// ----------------------------------------------------------------------
// Copyright (C) 2006 by Khaled Al-Shamaa.
// http://www.al-shamaa.com/
// ----------------------------------------------------------------------
// LICENSE

// This program is open source product; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Class Name: Arabic Glyphs is a simple class to render Arabic text
// Filename:   ArGlyphs.class.php
// Original    Author(s): Khaled Al-Sham'aa <khaled.alshamaa@gmail.com>
// Purpose:    This class takes Arabic text (encoded in Windows-1256 character set)
//             as input and performs Arabic glyph joining on it and outputs a UTF-8
//             hexadecimals stream that is no longer logically arranged but in
//             a visual order which gives readable results when formatted with
//             a simple Unicode rendering just like GD and UFPDF libraries that
//             does not handle basic connecting glyphs of Arabic language yet but
//             simply outputs all stand alone glyphs in left-to-right order.
// ----------------------------------------------------------------------

class ArGlyphs {
    var $glyphs = array();
    var $prevLink;
    var $nextLink;

    var $crntChar = NULL;
    var $prevChar = NULL;
    var $nextChar = NULL;

    function ArGlyphs() {
        $this->prevLink = array('¡', '¿', 'º', 'Ü', 'Æ', 'È', 'Ê', 'Ë', 'Ì',
            'Í', 'Î', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ý', 'Þ', 'ß',
            'á', 'ã', 'ä', 'å', 'í');
        $this->nextLink = array('Ü', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'Æ', 'È', 'É',
            'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö',
            'Ø', 'Ù', 'Ú', 'Û', 'Ý', 'Þ', 'ß', 'á', 'ã', 'ä', 'å', 'æ', 'ì',
            'í');
        $this->vowel = array('ð', 'ñ', 'ò', 'ó', 'õ', 'ö', 'ø', 'ú');

        /*
        $this->glyphs['ð']  = array('FE70','FE71');
        $this->glyphs['ñ']  = array('FE72','FE72');
        $this->glyphs['ò']  = array('FE74','FE74');
        $this->glyphs['ó']  = array('FE76','FE77');
        $this->glyphs['õ']  = array('FE78','FE79');
        $this->glyphs['ö']  = array('FE7A','FE7B');
        $this->glyphs['ø']  = array('FE7C','FE7D');
        $this->glyphs['ú']  = array('FE7E','FE7E');
         */

        $this->glyphs['ð'] = array('064B', '064B');
        $this->glyphs['ñ'] = array('064C', '064C');
        $this->glyphs['ò'] = array('064D', '064D');
        $this->glyphs['ó'] = array('064E', '064E');
        $this->glyphs['õ'] = array('064F', '064F');
        $this->glyphs['ö'] = array('0650', '0650');
        $this->glyphs['ø'] = array('0651', '0651');
        $this->glyphs['ú'] = array('0652', '0652');

        $this->glyphs['Á'] = array('FE80', 'FE80', 'FE80', 'FE80');
        $this->glyphs['Â'] = array('FE81', 'FE82', 'FE81', 'FE82');
        $this->glyphs['Ã'] = array('FE83', 'FE84', 'FE83', 'FE84');
        $this->glyphs['Ä'] = array('FE85', 'FE86', 'FE85', 'FE86');
        $this->glyphs['Å'] = array('FE87', 'FE88', 'FE87', 'FE88');
        $this->glyphs['Æ'] = array('FE89', 'FE8A', 'FE8B', 'FE8C');
        $this->glyphs['Ç'] = array('FE8D', 'FE8E', 'FE8D', 'FE8E');
        $this->glyphs['È'] = array('FE8F', 'FE90', 'FE91', 'FE92');
        $this->glyphs['É'] = array('FE93', 'FE94', 'FE93', 'FE94');
        $this->glyphs['Ê'] = array('FE95', 'FE96', 'FE97', 'FE98');
        $this->glyphs['Ë'] = array('FE99', 'FE9A', 'FE9B', 'FE9C');
        $this->glyphs['Ì'] = array('FE9D', 'FE9E', 'FE9F', 'FEA0');
        $this->glyphs['Í'] = array('FEA1', 'FEA2', 'FEA3', 'FEA4');
        $this->glyphs['Î'] = array('FEA5', 'FEA6', 'FEA7', 'FEA8');
        $this->glyphs['Ï'] = array('FEA9', 'FEAA', 'FEA9', 'FEAA');
        $this->glyphs['Ð'] = array('FEAB', 'FEAC', 'FEAB', 'FEAC');
        $this->glyphs['Ñ'] = array('FEAD', 'FEAE', 'FEAD', 'FEAE');
        $this->glyphs['Ò'] = array('FEAF', 'FEB0', 'FEAF', 'FEB0');
        $this->glyphs['Ó'] = array('FEB1', 'FEB2', 'FEB3', 'FEB4');
        $this->glyphs['Ô'] = array('FEB5', 'FEB6', 'FEB7', 'FEB8');
        $this->glyphs['Õ'] = array('FEB9', 'FEBA', 'FEBB', 'FEBC');
        $this->glyphs['Ö'] = array('FEBD', 'FEBE', 'FEBF', 'FEC0');
        $this->glyphs['Ø'] = array('FEC1', 'FEC2', 'FEC3', 'FEC4');
        $this->glyphs['Ù'] = array('FEC5', 'FEC6', 'FEC7', 'FEC8');
        $this->glyphs['Ú'] = array('FEC9', 'FECA', 'FECB', 'FECC');
        $this->glyphs['Û'] = array('FECD', 'FECE', 'FECF', 'FED0');
        $this->glyphs['Ý'] = array('FED1', 'FED2', 'FED3', 'FED4');
        $this->glyphs['Þ'] = array('FED5', 'FED6', 'FED7', 'FED8');
        $this->glyphs['ß'] = array('FED9', 'FEDA', 'FEDB', 'FEDC');
        $this->glyphs['á'] = array('FEDD', 'FEDE', 'FEDF', 'FEE0');
        $this->glyphs['ã'] = array('FEE1', 'FEE2', 'FEE3', 'FEE4');
        $this->glyphs['ä'] = array('FEE5', 'FEE6', 'FEE7', 'FEE8');
        $this->glyphs['å'] = array('FEE9', 'FEEA', 'FEEB', 'FEEC');
        $this->glyphs['æ'] = array('FEED', 'FEEE', 'FEED', 'FEEE');
        $this->glyphs['ì'] = array('FEEF', 'FEF0', 'FEEF', 'FEF0');
        $this->glyphs['í'] = array('FEF1', 'FEF2', 'FEF3', 'FEF4');
        $this->glyphs['áÂ'] = array('FEF5', 'FEF6', 'FEF5', 'FEF6');
        $this->glyphs['áÃ'] = array('FEF7', 'FEF8', 'FEF7', 'FEF8');
        $this->glyphs['áÅ'] = array('FEF9', 'FEFA', 'FEF9', 'FEFA');
        $this->glyphs['áÇ'] = array('FEFB', 'FEFC', 'FEFB', 'FEFC');

        $this->glyphs['Ü'] = array('0640', '0640', '0640', '0640');
        $this->glyphs['¡'] = array('060C', '060C', '060C', '060C');
        $this->glyphs['¿'] = array('061F', '061F', '061F', '061F');
        $this->glyphs['º'] = array('061B', '061B', '061B', '061B');
    }

    function pre_convert($str) {
        $crntChar = NULL;
        $prevChar = NULL;
        $nextChar = NULL;
        $output = '';

        $chars = preg_split('//', $str);
        $max = count($chars);

        for ($i = $max - 1; $i >= 0; $i--) {
            $crntChar = $chars[$i];
            $prevChar = @$chars[$i - 1];

            if (in_array($prevChar, $this->vowel)) {
                $prevChar = $chars[$i - 2];
                if (in_array($prevChar, $this->vowel)) {
                    $prevChar = $chars[$i - 3];
                }
            }

            $Reversed = false;
            $flip_arr = array(')', ']', '>', '}');
            $ReversedChr = array('(', '[', '<', '{');

            if (in_array($crntChar, $flip_arr)) {
                $crntChar = str_replace($flip_arr, $ReversedChr, $crntChar);
                $Reversed = true;
            } else {
                $Reversed = false;
            }

            if (in_array($crntChar, $ReversedChr) && !$Reversed) {
                $crntChar = str_replace($ReversedChr, $flip_arr, $crntChar);
            }

            if (in_array($crntChar, $this->vowel)) {
                if (in_array($chars[$i + 1], $this->nextLink) && in_array
                    ($prevChar, $this->prevLink)) {
                    $output .= '&#x'.$this->glyphs[$crntChar][1].';';
                } else {
                    $output .= '&#x'.$this->glyphs[$crntChar][0].';';
                }
                continue;
            }

            if (@in_array($chars[$i + 1], array('Â', 'Ã', 'Å', 'Ç')) &&
                $crntChar == 'á') {
                continue;
            }

            if (ord($crntChar) < 128) {
                $output .= $crntChar;
                $nextChar = $crntChar;
                continue;
            }

            $form = 0;

            if (in_array($crntChar, array('Â', 'Ã', 'Å', 'Ç')) && $prevChar ==
                'á') {
                if (in_array($chars[$i - 2], $this->prevLink)) {
                    $form = $form + 1;
                } $output .= '&#x'.$this
                    ->glyphs[$prevChar.$crntChar][$form].';';
                $nextChar = $prevChar;
                continue;
            }

            if (in_array($prevChar, $this->prevLink)) {
                $form = $form + 1;
            } if (in_array($nextChar, $this->nextLink)) {
                $form = $form + 2;
            }

            $output .= '&#x'.$this->glyphs[$crntChar][$form].';';
            $nextChar = $crntChar;
        }

        $output = $this->decode_entities($output, $exclude = array('&'));
        return $output;
    }

    function a4_max_chars($font) {
        $x = 381.6 - 31.57 * $font + 1.182 * pow($font, 2) - 0.02052 * pow
            ($font, 3) + 0.0001342 * pow($font, 4);
        return floor($x - 2);
    }

    function a4_lines($str, $font) {
        $str = str_replace(array("\r\n", "\n", "\r"), "\n", $str);

        $lines = 0;
        $chars = 0;
        $words = preg_split("/[\s,]+/", $str); //split(' ', $str);
        $w_count = count($words);
        $max_chars = $this->a4_max_chars($font);

        for ($i = 0; $i < $w_count; $i++) {
            $w_len = strlen($words[$i]) + 1;

            if ($chars + $w_len < $max_chars) {
                if (preg_match("/\n/i", $words[$i])) {
                    $words_nl = preg_split("/\n/", $words[$i]); //split("\n", $words[$i]);

                    $nl_num = count($words_nl) - 1;
                    for ($j = 1; $j < $nl_num; $j++) {
                        $lines++;
                    }

                    $chars = strlen($words_nl[$nl_num]) + 1;

                } else {
                    $chars += $w_len;
                }
            } else {
                $lines++;
                $chars = $w_len;
            }
        }
        $lines++;

        return $lines;
    }

    function convert($str, $max_chars = 50, $hindo = true) {
        $str = str_replace(array("\r\n", "\n", "\r"), "\n", $str);

        $lines = array();
        $words = preg_split("/[\s,]+/", $str); //split(' ', $str);
        $w_count = count($words);
        $c_chars = 0;
        $c_words = array();

        $english = array();
        $en_index =  - 1;

        for ($i = 0; $i < $w_count; $i++) {
            if (preg_match(
                "/^[a-z\d\\/\!\@\#\$\%\^\&\*\(\)\-\_\+\=\~\:\"\'\[\]\{\}\;\,\.\|]*$/i", $words[$i])) {
                $words[$i] = strrev($words[$i]);
                array_push($english, $words[$i]);
                if ($en_index ==  - 1) {
                    $en_index = $i;
                }
            } elseif ($en_index !=  - 1) {
                $en_count = count($english);

                for ($j = 0; $j < $en_count; $j++) {
                    $words[$en_index + $j] = $english[$en_count - 1-$j];
                }

                $en_index =  - 1;
                $english = array();
            }

            $en_count = count($english);

            for ($j = 0; $j < $en_count; $j++) {
                $words[$en_index + $j] = $english[$en_count - 1-$j];
            }
        }

        for ($i = 0; $i < $w_count; $i++) {
            $w_len = strlen($words[$i]) + 1;


            if ($c_chars + $w_len < $max_chars) {
                if (preg_match("/\n/i", $words[$i])) {
                    $words_nl = preg_split("/\n/", $words[$i]); //split("\n", $words[$i]);

                    array_push($c_words, $words_nl[0]);
                    array_push($lines, implode(' ', $c_words));

                    $nl_num = count($words_nl) - 1;
                    for ($j = 1; $j < $nl_num; $j++) {
                        array_push($lines, $words_nl[$j]);
                    }

                    $c_words = array($words_nl[$nl_num]);
                    $c_chars = strlen($words_nl[$nl_num]) + 1;

                } else {
                    array_push($c_words, $words[$i]);
                    $c_chars += $w_len;
                }
            } else {
                //array_push($c_words, str_repeat(' ', $max_chars - $c_chars - 1));
                array_push($lines, implode(' ', $c_words));
                $c_words = array($words[$i]);
                $c_chars = $w_len;
            }
        }
        array_push($lines, implode(' ', $c_words));

        $max_line = count($lines);
        $output = '';
        for ($j = $max_line - 1; $j >= 0; $j--) {
            $output .= $lines[$j]."\n";
        }

        $output = rtrim($output);

        $output = $this->pre_convert($output);
        if ($hindo) {
            $Nums = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
            $arNums = array('Ù ', 'Ù¡', 'Ù¢', 'Ù£', 'Ù¤', 'Ù¥', 'Ù¦', 'Ù§',
                'Ù¨', 'Ù©');
            $output = str_replace($Nums, $arNums, $output);
        }

        return $output;
    }

    /**
     * Decode all HTML entities (including numerical ones) to regular UTF-8 bytes.
     * Double-escaped entities will only be decoded once ("&amp;lt;" becomes "&lt;", not "<").
     *
     * @param $text
     *   The text to decode entities in.
     * @param $exclude
     *   An array of characters which should not be decoded. For example,
     *   array('<', '&', '"'). This affects both named and numerical entities.
     */
    function decode_entities($text, $exclude = array()) {
        static $table;
        // We store named entities in a table for quick processing.
        if (!isset($table)) {
            // Get all named HTML entities.
            $table = array_flip(get_html_translation_table(HTML_ENTITIES));
            // PHP gives us ISO-8859-1 data, we need UTF-8.
            $table = array_map('utf8_encode', $table);
            // Add apostrophe (XML)
            $table['&apos;'] = "'";
        }
        $newtable = array_diff($table, $exclude);
        // Use a regexp to select all entities in one pass, to avoid decoding double-escaped entities twice.
        return preg_replace('/&(#x?)?([A-Za-z0-9]+);/e', '$this
            ->_decode_entities("$1", "$2", "$0", $newtable, $exclude)', $text);
    }

    /**
     * Helper function for decode_entities
     */
    function _decode_entities($prefix, $codepoint, $original, &$table,
        &$exclude) {
        // Named entity
        if (!$prefix) {
            if (isset($table[$original])) {
                return $table[$original];
            } else {
                return $original;
            }
        }

        // Hexadecimal numerical entity
        if ($prefix == '#x') {
            $codepoint = base_convert($codepoint, 16, 10);
        }

        // Encode codepoint as UTF-8 bytes
        if ($codepoint < 0x80) {
            $str = chr($codepoint);
        } else if ($codepoint < 0x800) {
            $str = chr(0xC0 | ($codepoint >> 6)).chr(0x80 | ($codepoint & 0x3F))
                ;
        } else if ($codepoint < 0x10000) {
            $str = chr(0xE0 | ($codepoint >> 12)).chr(0x80 | (($codepoint >> 6)
                & 0x3F)).chr(0x80 | ($codepoint & 0x3F));
        } else if ($codepoint < 0x200000) {
            $str = chr(0xF0 | ($codepoint >> 18)).chr(0x80 | (($codepoint >> 12)
                & 0x3F)).chr(0x80 | (($codepoint >> 6) & 0x3F)).chr(0x80 |
                ($codepoint & 0x3F));
        }

        // Check for excluded characters
        if (in_array($str, $exclude)) {
            return $original;
        } else {
            return $str;
        }
    }
}

?>
