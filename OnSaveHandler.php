<?php
    namespace Internalize
    {
        class OnSaveHandler
        {
            static function OnPageContentSave(&$wikiPage, &$user, &$content, &$summary, $isMinor, $isWatch, $section, &$flags, &$status)
            {
                $needles = [
                    "http://howtobeahero.de",
                    "https://howtobeahero.de",
                    "http://howtobeahero.de/",
                    "https://howtobeahero.de/",
                    "http://www.howtobeahero.de",
                    "https://www.howtobeahero.de",
                    "http://www.howtobeahero.de/",
                    "https://www.howtobeahero.de/"
                ];
                $rawContent = $content->getNativeData();

                global $wgServer, $wgScriptPath;
                $wikiPath = $wgServer.$wgScriptPath;
                $wikiPath = str_replace("/", "\\/", $wikiPath);
                $findLinksRegex = "/$wikiPath\/(?:index\.php(?:\?title=|\/)?)?(\S*)/";
                preg_match_all($findLinksRegex, $rawContent, $matches, PREG_SET_ORDER, 0);
                foreach($matches as $match)
                {
                    $url = parse_url($match[0]);
                    if (array_key_exists("query", $url))
                    {
                        parse_str($url["query"], $output);
                        if (array_key_exists("title", $output))
                        {
                            $title = $output["title"];
                            unset($output["title"]);
                            $query = http_build_query($output);
                        }
                    }
                    else
                    {
                        $title = $match[1];
                    }

                    var_dump($title);

                    if (strlen($query) != 0)
                    {
                        $content = "[{{fullurl:$title|$query}} {{PAGENAME:$title}}]";
                    }
                    else
                    {
                        $content = "[[".$title."]]";
                    }
                    $rawContent = str_replace($match[0], $content, $rawContent);
                }

                $content = new \WikitextContent($rawContent);
            }
        }
    }
