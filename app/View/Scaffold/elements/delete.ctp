<div class="row">
    <div class="col-md-12">
        <div class="portlet box green-meadow">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-<?=$this->Scaffold->getIconData()?>"></i> <?=$this->Scaffold->getLabelData()?>
                </div>
                <div class="tools">
                    <a class="collapse" href="javascript:;">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <?=$this->Scaffold->dumpDetailData()?>
            </div>
        </div>
    </div>
</div>

<form role="form" id="scaffold" accept-charset="utf-8" method="post" enctype="multipart/form-data">       
    <?=$this->Scaffold->submit()?>
</form>