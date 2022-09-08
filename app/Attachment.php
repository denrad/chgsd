<?php

declare(strict_types=1);

namespace app;

use app\value\File;
use voku\helper\HtmlDomParser;

class Attachment
{
    public function __construct(private readonly HtmlDomParser $parser)
    {
    }

    public function getAttachment(string $url): File
    {
        $dom = $this->parser->load_file($url);
        $attachmentUrl = $dom->findOne('.attachfile_name')->getAttribute('href');
        if ($attachmentUrl) {
            try {
                if ($content = file_get_contents($url)) {
                    return new File(
                        new \CURLStringFile($content, 'document'),
                        basename($attachmentUrl)
                    );
                }
            } catch (\Throwable) {
            }
        }
        return new File(null, null);
    }

}
