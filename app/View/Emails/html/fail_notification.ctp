<p>Ha finalizado, con error, el Crawler para <?= $CrawlerLog->Data()->read('Target') ?>.</p>
<?=$this->element('email/notification', [ 'CrawlerLog' => $CrawlerLog ])?>
