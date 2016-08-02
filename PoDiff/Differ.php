<?php
namespace PoDiff;

use Sepia\PoParser;

class Differ
{
    protected $_oldFilePath;
    protected $_newFilePath;

    public function __construct($oldFilePath, $newFilePath)
    {
        $this->_oldFilePath = $oldFilePath;
        $this->_newFilePath = $newFilePath;
    }

    public function getDiff()
    {
        $oldFileParser = PoParser::parseFile($this->_oldFilePath);
        $newFileParser = PoParser::parseFile($this->_newFilePath);
        $oldEntries = $oldFileParser->getEntries();
        $newEntries = $newFileParser->getEntries();
        $ret = array();
        $ret['changed'] = array();
        $ret['removed'] = array();
        $ret['added'] = array();
        foreach ($oldEntries as $key => $oldEntry) {
            if (!$key) continue;
            if (isset($newEntries[$key])) {
                $changed = false;
                $newEntryImploded = array();
                foreach ($newEntries[$key] as $a => $value) {
                    if (strpos($a, 'msgstr') !== 0) continue;
                    $newEntryImploded[$a] = isset($newEntries[$key][$a]) ? implode($newEntries[$key][$a]): '';
                }

                foreach ($oldEntry as $a => $value) {
                    if (strpos($a, 'msgstr') !== 0) continue;
                    $oldEntryImploded[$a] = isset($oldEntry[$a]) ? implode($oldEntry[$a]): '';

                    if (isset($newEntryImploded[$a])) {
                        if ($newEntryImploded[$a] != $oldEntryImploded[$a]) {
                            $changed = true;
                            break;
                        } else {
                            unset($newEntryImploded[$a]);
                        }
                    }
                }

                if (!$changed) {
                    foreach ($newEntryImploded as $a => $value) {
                        if (isset($oldEntryImploded[$a])) {
                            if ($oldEntryImploded[$a] != $value) {
                                $changed = true;
                                break;
                            }
                        }
                    }
                }

                if ($changed) {
                    $ret['changed'][] = array(
                        'old' => $oldEntry,
                        'new' => $newEntries[$key]
                    );
                }
                unset($newEntries[$key]);
            } else {
                $ret['removed'][] = $oldEntry;
            }
        }
        unset($newEntries['']);
        $ret['added'] = array_values($newEntries);
        return $ret;
    }
}
