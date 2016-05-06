<tr>
<?php
    if($CrawlerLog->isReadable($field)){
        echo $this->element('email/label',[
            'label' => $meta['label']
        ]);

        echo $this->element('email/value',[
            'meta' => $meta,
            'value' => $CrawlerLog->getViewValue($field)
        ]);
    }
?>
</tr>