<?php

/**
 * Function to extract all translate-strings from twig-templates
 */

namespace svenanders\tools;

class SarPoTwigExtractor
{

    private $folder;
    private $twigFiles;
    private $poFiles;
    private $poStrings;

    public function __construct($folder)
    {
        $this->folder = $folder;
        if ($this->_validDir()) {

        }
    }

    private function _validDir()
    {
        return is_dir($this->folder) ? true : false;
    }

    /**
     * Get an array that represents directory tree
     */
    private function _directoryToArray($directory, $recursive = true, $listDirs = false, $listFiles = true, $exclude = '')
    {
        $arrayItems = array();
        $skipByExclude = false;
        $handle = opendir($directory);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
                if ($exclude) {
                    preg_match($exclude, $file, $skipByExclude);
                }
                if (!$skip && !$skipByExclude) {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $file)) {
                        if ($recursive) {
                            $arrayItems = array_merge($arrayItems, $this->_directoryToArray($directory . DIRECTORY_SEPARATOR . $file, $recursive, $listDirs, $listFiles, $exclude));
                        }
                        if ($listDirs) {
                            $file = $directory . DIRECTORY_SEPARATOR . $file;
                            $arrayItems[] = $file;
                        }
                    } else {
                        if ($listFiles) {
                            $file = $directory . DIRECTORY_SEPARATOR . $file;
                            $arrayItems[] = $file;
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $arrayItems;
    }

    private function _getTwigAndPoFiles()
    {
        $files = $this->_directoryToArray($this->folder);
        $twigFiles = [];
        $poFiles = [];
        foreach ($files as $file) {
            $ext = explode(".", $file);
            if ($ext[count($ext) - 1] == "twig") {
                $twigFiles[] = $file;
            }
            if ($ext[count($ext) - 1] == "po") {
                $poFiles[] = $file;
            }
        }
        $this->poFiles = $poFiles;
        $this->twigFiles = $twigFiles;
    }

    public function parseModule()
    {
        $this->_getTwigAndPoFiles();
        $this->poStrings = $this->_fetchPoStringsFromFiles();

        $newStrings = $this->_fetchTwigStringsFromFiles();
        $this->_updatePoFiles($newStrings);

        echo "Finished parsing PO-files".PHP_EOL;

    }

    private function _fetchTwigStringsFromFiles()
    {
        $resultArray = [];

        foreach ($this->twigFiles as $file) {
            $fp = fopen($file, 'r');
            $regex = '/\((\'.*?\')\)/';
            while (($line = fgets($fp)) !== false) {
                while (preg_match($regex, $line, $matches, PREG_OFFSET_CAPTURE)) {
                    $last = end($matches);
                    $line = substr($line, $last[1] + strlen($last[0]) + 1);

                    for ($i = 1; $i < count($matches); $i++) {
                        if (!in_array(substr($matches[$i][0], 1, -1), $resultArray) &&
                            !in_array(substr($matches[$i][0], 1, -1), $this->poStrings)
                        )
                            $resultArray[] = substr($matches[$i][0], 1, -1);
                    }

                }

            }

        }
        return $resultArray;
    }

    private function _fetchPoStringsFromFiles()
    {
        $resultArray = [];

//        var_dump($this->poFiles);
        foreach ($this->poFiles as $file) {
            $fp = fopen($file, 'r');
            $regex = '/(\msgid .*?\".*?\")/';
            while (($line = fgets($fp)) !== false) {
                while (preg_match($regex, $line, $matches, PREG_OFFSET_CAPTURE)) {
                    $last = end($matches);
                    $line = substr($line, $last[1] + strlen($last[0]) + 1);

                    for ($i = 1; $i < count($matches); $i++) {
                        $message = substr(str_replace('msgid "', '', $matches[$i][0]), 0, -1);
                        if (!in_array($matches[$i][0], $resultArray)) {
                            !empty($message) ? $resultArray[] = $message : '';
                        }
                    }

                }

            }

        }
        return $resultArray;
    }

    private function _updatePoFiles($newStringsArray)
    {
        foreach ($this->poFiles as $file) {
            $input = file_get_contents($file);
            if (!empty($newStringsArray)) {
                foreach ($newStringsArray as $string) {
                    $nostring='';
                    $input .= "\n";
                    $input .= 'msgid "' . $string . "\"\n";
                    $input .= 'msgstr "' . $nostring . "\"\n";
                    // you can replace nostring with string to automatically
                    // add the original text as the default 'translation'
                }
                $langFile= explode("/",$file);
                echo "Updating ".$langFile[count($langFile)-1]."\n";
                file_put_contents($file, $input);
            }
        }
    }

}


