<table>
<?php
    $schema = $CrawlerLog->getSchema();
    
    foreach($schema as $field => $meta){
        echo $this->element('email/row',[ 
            'CrawlerLog' => $CrawlerLog,
            'field' => $field,
            'meta' => $meta,
        ]);
    }
?>
</table>