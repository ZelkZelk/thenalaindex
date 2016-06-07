var SingleAbmRow = React.createClass({    
    protTypes: {
        id : React.PropTypes.string.isRequired,
        value : React.PropTypes.string.isRequired, 
        dropApi : React.PropTypes.string.isRequired,
        editApi : React.PropTypes.string.isRequired,
        env: React.PropTypes.array.isRequired,     
        dropCallback : React.PropTypes.func.isRequired,
        editCallback : React.PropTypes.func.isRequired,
    },
    states : {
        idle : 0,
        edit : 1,
        drop : 2,
        editSend : 3,
        dropSend : 4,
        editFail : 5,
        dropFail : 6
    },
    getInitialState : function(){
        return {
            action : this.states.idle,
            input : this.props.value
        };
    },
    apiLag : 2000,
    valueColWidth : "75%",
    resolvState : function(){
        var state = this.states.idle;
        
        if(this.state !== null){
            if(typeof this.state.action !== 'undefined'){
                switch(this.state.action){
                    case this.states.edit:
                    case this.states.drop:
                    case this.states.idle:
                    case this.states.editSend:
                    case this.states.dropSend:
                    case this.states.editFail:
                    case this.states.dropFail:
                        state = this.state.action;
                        break;
                }
            }
        }
        
        return state;
    },
    resolvUI : function(){
        var state = this.resolvState();
        var ui = "";
        
        switch(state){
            case this.states.edit:
                ui = this.resolvEditUI();
                break;
            case this.states.idle:
                ui = this.resolvIdleUI();
                break;
            case this.states.drop:
                ui = this.resolvDropUI();
                break;
            case this.states.dropSend:
                ui = this.resolvDropSendUI();
                break;
            case this.states.editSend:
                ui = this.resolvEditSendUI();
                break;
            case this.states.editFail:
                ui = this.resolvEditFailUI();
                break;
            case this.states.dropFail:
                ui = this.resolvDropFailUI();
                break;
        }
        
        return ui;
    },
    resolvEditDivClass : function(){
        var className = "input-icon right";
        
        if(this.isFail()){
            className += " has-error";
        }
        
        return className;
    },
    resolvErrorMessage : function(){
        var error = "";
        
        if(this.isFail()){
            error = <div style={{ color : "#AF0000" }}>{this.state.message}</div>
        }
        
        return error;
    },
    isFail : function(){
        var isFail = false;
        
        if(this.state !== null){
            if(typeof this.state.action !== 'undefined'){
                switch(this.state.action){
                    case this.states.editFail:
                    case this.states.dropFail:
                        isFail = true;
                        break;
                }
            }
        }
        
        return isFail;
    },
    resolvEditUI : function(){
        var value = this.state.input;
        var inputDivClass = this.resolvEditDivClass();
        var errorMessage = this.resolvErrorMessage();
        
        var ui = (
            <tr>
                <td width={this.valueColWidth}>
                    <div className={inputDivClass}>
                        <i className="fa fa-edit"></i>
                        <input className="form-control form-cascade-control" type="text" defaultValue={value} ref={ (ref) => this.input = ref }/>
                    </div>
            
                    {errorMessage}
                </td>
                <td style={{ textAlign : 'right' }}>                                        
                    <button onClick={this.editConfirmClick.bind(null,this.props)} className="btn btn-success" style={{ cursor : 'pointer' }}>
                        EDITAR
                    </button>
            
                    <button onClick={this.editCancelClick.bind(null,this.props)} className="btn btn-danger" style={{ cursor : 'pointer' }}>
                        CANCELAR
                    </button>
                </td>
            </tr>
        );

        return ui;        
    },
    resolvEditFailUI : function(){
        return this.resolvEditUI();
    },
    resolvEditSendUI : function(){
        var ui = (
            <tr>
                <td width={this.valueColWidth}><input disabled="disabled" className="form-control" type="text" value={this.state.input} ref={ (ref) => this.input = ref }/></td>
                <td style={{ textAlign : 'right' }}> 
                    <img src="/img/flipflop.gif" width="30px" height="30px" />
                </td>
            </tr>
        );

        return ui;        
    },
    resolvDropSendUI : function(){
        var ui = (
            <tr>
                <td width={this.valueColWidth}>{this.props.value}</td>
                <td style={{ textAlign : 'right' }}> 
                    <img src="/img/flipflop.gif" width="30px" height="30px" />
                </td>
            </tr>
        );

        return ui;        
    },
    resolvDropFailUI : function(){
        var errorMessage = this.resolvErrorMessage();
        
        var ui = (
            <tr>
                <td width={this.valueColWidth}>
                    {this.props.value}
                    {errorMessage}
                </td>
                <td style={{ textAlign : 'right' }}>                                    
                    <button onClick={this.dropConfirmClick.bind(null,this.props)} className="btn btn-success" style={{ cursor : 'pointer' }}>
                        ELIMINAR
                    </button>
            
                    <button onClick={this.dropCancelClick.bind(null,this.props)} className="btn btn-danger" style={{ cursor : 'pointer' }}>
                        CANCELAR
                    </button>
                </td>
            </tr>
        );

        return ui;        
    },
    resolvDropUI : function(){
        var ui = (
            <tr>
                <td width={this.valueColWidth}>
                    {this.props.value}
                </td>
                <td style={{ textAlign : 'right' }}>                                    
                    <button onClick={this.dropConfirmClick.bind(null,this.props)} className="btn btn-success" style={{ cursor : 'pointer' }}>
                        ELIMINAR
                    </button>
            
                    <button onClick={this.dropCancelClick.bind(null,this.props)} className="btn btn-danger" style={{ cursor : 'pointer' }}>
                        CANCELAR
                    </button>
                </td>
            </tr>
        );

        return ui;        
    },
    resolvIdleUI : function(){
        var ui = (
            <tr>
                <td width={this.valueColWidth}>{this.props.value}</td>
                <td style={{ textAlign : 'right' }}>
                    <button onClick={this.editClick.bind(null,this.props)} ref={ (ref) => this.editButton = ref } className="btn btn-warning" style={{ cursor : 'pointer' }}>
                        <i className="fa fa-edit"></i>
                    </button>
            
                    <button onClick={this.dropClick.bind(null,this.props)} ref={ (ref) => this.dropButton = ref } className="btn btn-warning" style={{ cursor : 'pointer' }}>
                        <i className="fa fa-ban"></i>
                    </button>
                </td>
            </tr>
        );

        return ui;
    },
    input : null,
    idle : function(id,value){
        this.setState({
            'action' : this.states.idle,
            'id' : id,
            'value' : value
        });
    },
    editButton : null,
    editClick : function(data,event){
        this.edit();
    },
    editConfirmClick : function(data,event){
        this.confirmEdit();
    },
    confirmEdit : function(){
        this.editSend();
    },
    editCancelClick : function(data,event){
        this.idle();
    },
    edit : function(){
        if(this.editButton !== null){
            this.setState({
                'action' : this.states.edit,
                'input' : this.props.value
            },function(){
                this.input.focus();
            });
        }
    },
    dropButton : null,
    dropClick : function(data,event){
        this.drop();
    },
    dropConfirmClick : function(data,event){
        this.dropSend();
    },
    dropCancelClick : function(data,event){
        this.idle();
    },
    drop : function(){
        if(this.dropButton !== null){
            this.setState({
                'action' : this.states.drop
            });
        }
    },
    dropSend : function(){
        this.setState({
            'action' : this.states.dropSend,
        });
        
        var self = this;
        
        this.timer = setTimeout(function(){
            self.dropSendAjax();
        },this.apiLag);
    },
    editSend : function(){
        this.setState({
            'action' : this.states.editSend,
            'input' : this.input.value
        });
        
        var self = this;
        
        this.timer = setTimeout(function(){
            self.editSendAjax();
        },this.apiLag);
    },
    dropSendAjax : function(){
        this.ajaxHit();
        
        var self = this;
        
        this.xhr = $.ajax({
            url : this.props.dropApi,
            method : 'POST',
            data : {
                id : this.props.id,
                env : this.props.env
            }
        }).success(function(data){
            self.dropAjaxSuccess(data);
        }).error(function(event){            
            self.dropAjaxError(event);
        }).always(function(){            
            self.xhr = null;
        });
    },
    dropAjaxSuccess : function(data){
        this.idle();
        this.props.dropCallback(data);
    },
    dropAjaxError : function(event){
        this.idle();
        this.dropFail(event.responseText);
    },
    editAjaxError : function(event){        
        this.editFail(event.responseText);
    },
    editSendAjax : function(){
        this.ajaxHit();
        
        var self = this;
        
        this.xhr = $.ajax({
            url : this.props.editApi,
            method : 'POST',
            data : {
                id : this.props.id,
                value : this.state.input,
                env : this.props.env
            }
        }).success(function(data){
            self.editAjaxSuccess(data);
        }).error(function(event){            
            self.editAjaxError(event);
        }).always(function(){            
            self.xhr = null;
        });
    },
    editAjaxSuccess : function(data){
        var value = null;
        var id = null;

        for(var i in data){
            value = data[i];
            id = i;
            break;
        }

        if(value == null || id == null){                
            this.editAjaxError({
                responseText : 'No se obtuvo una respuesta valida del Servidor'
            });
        }
        else{
            this.idle();
            this.props.editCallback(id,value);
        }
    },
    editAjaxError : function(event){
        this.editFail(event.responseText);
    },
    dropFail : function(message){
        this.setState({
            action : this.states.dropFail,
            message : message
        });
    },
    editFail : function(message){
        this.setState({
            action : this.states.editFail,
            message : message
        });
    },
    xhr : null,
    ajaxHit : function(){
        if(this.xhr !== null){
            this.xhr.abort();
        }
    },
    render : function(){          
        var ui = this.resolvUI()
        
        return ui;
    },
});

module.exports = SingleAbmRow