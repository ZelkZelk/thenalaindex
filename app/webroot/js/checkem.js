var $Checkem = {
    
};

function checkem() {
    this.value = false;
    this.holder = null;
    this.name = '';
    this.trigger = null;
    this.options = {};
    this.parent = null;
    this.checkbox = null;
    
    this.boot = function(checkbox,options){
        this.name = checkbox.attr('name');
        this.value = checkbox.is(':checked');
        this.parent = checkbox.parent();
        this.options = options;
        this.checkbox = checkbox;
        
        checkbox.css({
            position : 'absolute',
            left : '-1200px',
            top : '-1200px'
        });
        
        this.render();
        this.toggle();
        this.binding();
    };
    
    this.getText = function(){        
        if(this.value){            
            return this.options.state[1];
        }
        else{
            return this.options.state[0];
        }
    };
    
    this.getClass = function(){
        if(this.value){            
            return 'fa-check';
        }
        else{
            return 'fa-times';
        }
    };
    
    this.render = function(){
        if(this.trigger === null){
            this.trigger = $('<div class="input-icon right">'+
                                '<i class="fa"></i>' +
                                '<input type="text" readonly="readonly" style="cursor:pointer;" class="form-cascade-control form-control" value="">' +
                            '</div>');
                    
            this.trigger.attr('name',this.name);
            this.parent.append(this.trigger);
        }
    };
    
    this.binding = function(){
        $Checkem[this.name] = this;
        
        this.trigger.off('click').on('click',function(){        
            var name = $(this).attr('name');
            
            if($Checkem[name] != null){
                var checked = $Checkem[name];
                checked.change();
            }
        });
    };
    
    this.change = function(){
        this.value = !this.value;
        this.toggle();
    }
    
    this.toggle = function(){
        this.trigger.find('input').val(this.getText());
        this.trigger.find('i').removeClass('fa-times fa-check');
        this.trigger.find('i').addClass(this.getClass());
        this.checkResolv();
    };
    
    this.checkResolv = function(){        
        this.checkbox.prop('checked',this.value);
    };
};