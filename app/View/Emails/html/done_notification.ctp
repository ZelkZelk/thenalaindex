<p>Ha finalizado, satisfactoriamente, el Crawler para <?= $CrawlerLog->Data()->read('Target') ?>.</p>
<?=$this->element('email/notification', [ 'CrawlerLog' => $CrawlerLog ])?>
