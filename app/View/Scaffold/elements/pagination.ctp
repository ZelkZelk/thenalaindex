<div class="row">        
    <div class="col-md-12">        
        <div class="portlet light form-group">
            <div class="portlet-body">     
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>                                                      
                            <tr>                                
                                <?php $this->Scaffold->headers(); ?>
                            </tr>
                        </thead>
                        <tbody>                                
                            <?php $this->Scaffold->rows(); ?>
                        </tbody>
                        <tfoot>
                            <?php $this->Scaffold->numbers(); ?>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>	
</div>
