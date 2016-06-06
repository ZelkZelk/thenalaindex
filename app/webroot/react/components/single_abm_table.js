var SingleAbmRow = require('./single_abm_row.js');


var SingleAbmTable = React.createClass({
    protTypes: {
        icon : React.PropTypes.string.isRequired,
        title : React.PropTypes.string.isRequired,
        field : React.PropTypes.string.isRequirFed,
        fieldLabel: React.PropTypes.string.isRequired,
        fieldIcon: React.PropTypes.string.isRequired,
        feedApi: React.PropTypes.string.isRequired,
        pushApi: React.PropTypes.string.isRequired,
        dropApi: React.PropTypes.string.isRequired,
        editApi: React.PropTypes.string.isRequired,
        emptyText: React.PropTypes.string.isRequired,
        env: React.PropTypes.array,
    },
    apiLag : 2000,
    start : function(){
        this.cleanSleepContent();
        this.cleanSleepPush();
        this.cleanError();
        this.cleanXhrContent();
        this.cleanXhrPush();

        this.setState({
            'content' : this.contentStates.idle,
            'push' : this.pushStates.idle,
            'data' : []
        });

        this.fetch();
    },
    contentStates : {
        idle : -1,
        fetching : 0,
        empty : 1,
        filled : 2
    },
    pushStates : {
        idle : 0,
        sending : 1,
        failed : 2
    },
    error : null,
    setError : function(message){
        this.error = message;
    },
    cleanError : function(){
        this.error = null;
    },
    resolvError : function(){
        var error = "";

        if(this.error !== null){
            error =  (
                <div className="alert alert-danger">
                    <strong>Error! </strong>
                    {this.error}
                </div>
            );
        }

        return error;
    },
    input : null,
    xhrPush : null,
    pushButton : null,
    push : function(data,e){
        if(this.pushButton == null){
            return;
        }

        if(this.xhrAdd != null){
            return;
        }

        this.pushSend();
    },
    pushIdle : function(data){
        var content = this.contentStates.empty;

        if(this.hasData(data)){
            content = this.contentStates.filled;
        }

        this.setState({
            'content' : content,
            'push' : this.pushStates.idle,
            'data' : data
        });
    },
    pushFail : function(message){
        this.setState({
            'push' : this.pushStates.failed,
            'message' : message
        });
    },
    pushSend : function(){
        this.cleanError();

        this.setState({
            'push' : this.pushStates.sending
        });

        var self = this;

        this.sleepPush = setTimeout(function(){
            self.pushApi();
        },this.apiLag);
    },
    cleanSleepPush : function(){
        if(this.sleepPush != null){
            clearTimeout(this.sleepPush);
        }

        this.sleepContent = null;
    },
    sleepPush : null,
    readEnv : function(){
        var env = {};

        if(typeof this.props.env !== 'undefined'){
            for(var i in this.props.env){
                var v = this.props.env[i];

                if(typeof v !== 'function'){
                    env[i] = v;
                }
            }
        }

        return env;
    },
    pushApi : function(){
        var self = this;
        var envData = this.readEnv()

        this.xhrPush = $.ajax({
            url : this.props.pushApi,
            method : 'POST',
            data : {
                env : envData,
                value : this.input.value
            }
        }).success(function(data){
            self.pushAjaxSuccess(data);
        }).error(function(event){
            self.pushAjaxError(event);
        }).always(function(){
            self.xhrPush = null;
        });
    },
    pushAjaxSuccess : function(data){
        var newData = this.state.data;

        if(this.hasData(data)){
            for(var id in data){
                var value = data[id];
                newData[id] = value;
                break;
            }
        }

        this.input.value = "";
        this.pushIdle(newData);
    },
    pushAjaxError : function(event){
        switch(event.status){
            case 406:
                this.pushFail(event.responseText);
                break;
            case 500:
                this.setError("Falló la comunicación con el Servidor");
                this.pushIdle(this.state.data);
                break
        }
    },
    cleanSleepContent : function(){
        if(this.sleepContent != null){
            clearTimeout(this.sleepContent);
        }

        this.sleepContent = null;
    },
    sleepContent : null,
    fetch : function(){
        this.cleanError();

        this.setState({
            'content' : this.contentStates.fetching
        });

        var self = this;
        this.cleanSleepContent();

        this.sleepContent = setTimeout(function(){
            self.feedApi()
        },this.apiLag);

    },
    xhrContent : null,
    cleanXhrContent : function(){
        if(this.xhrContent !== null){
            this.xhrContent.abort();
        }

        this.xhrContent = null;
    },
    cleanXhrPush : function(){
        if(this.xhrPush !== null){
            this.xhrPush.abort();
        }

        this.xhrPush = null;
    },
    contentHit : function(){
        this.cleanXhrContent();
        this.cleanError();
    },
    feedApi : function(){
        var self = this;
        var envData = this.readEnv()
        self.contentHit();

        this.xhrContent = $.ajax({
            url : this.props.feedApi,
            type : 'post',
            data : {
                env : envData
            }
        }).success(function(data){
            self.feedApiSuccess(data);
        }).error(function(event){
            self.feedApiError(event);
        }).always(function(){
            self.xhrContent = null;
        });
    },
    feedApiError : function(event){
        this.setError("Falló la comunicación con el Servidor");
        this.empty();
    },
    hasData : function(data){
        var hasData = false;

        /* Una forma poco ortodoxa de saber si hay algo en un array, pero
         * funciona tambien para singletons. */

        for(var i in data){
            hasData = true;
            break;
        }

        return hasData;
    },
    feedApiSuccess : function(data){
        try{
            if(this.hasData(data)){
                this.fill(data);
            }
            else{
                this.empty();
            }
        }
        catch(e){
            this.feedApiError();
        }
    },
    empty : function(){
        this.setState({
            'content' : this.contentStates.empty
        });
    },
    fill : function(data){
        this.setState({
            'content' : this.contentStates.filled,
            'data' : data
        });
    },
    resolvContent : function(){
        var content = "";
        var state = this.contentStates.idle;

        if(this.state !== null){
            if(typeof this.state.content !== 'undefined'){
                state = this.state.content;
            }
        }

        switch(state){
            case this.contentStates.fetching:
                content = this.fetchingContent();
                break;
            case this.contentStates.empty:
                content = this.emptyContent();
                break;
            case this.contentStates.filled:
                content = this.filledContent();
                break;
        }

        return content;
    },
    fetchingContent : function(){
        return (
            <div className="portlet-body">
                <div id="single_abm_fetch" className="row">
                    <div className="col-md-12" style={{ padding:"40px 0px 40px 0px", textAlign:'center' }}>
                        <img src="/img/flipflop.gif" width="30px" height="30px" />
                    </div>
                </div>
            </div>
        );
    },
    emptyContent : function(){
        return (
            <div className="portlet-body">
                <div id="single_abm_empty" className="row">
                    <div className="col-md-12">
                        <p>{this.props.emptyText}</p>
                    </div>
                </div>
            </div>
        );
    },
    getRows : function(){
        var data = this.state.data;

        return <tbody>
                    {data.map((value,i) => {
                            return <SingleAbmRow
                                        dropApi={this.props.dropApi}
                                        editApi={this.props.editApi}
                                        dropCallback={this.dropCallback}
                                        editCallback={this.editCallback}
                                        key={i}
                                        id={i}
                                        env={this.readEnv()}
                                        value={value} />;
                    })}
                </tbody>;
    },
    dropErrorCallback : function(message){
        this.error = message;
        this.forceUpdate();
    },
    editCallback : function(id,value){
        var push = this.pushStates.idle;
        var content = this.contentStates.filled;
        var newData = [];

        if(typeof this.state.data !== null){
            newData = this.state.data;
        }

        newData[id] = value;

        if( ! this.hasData(newData)){
            content = this.contentStates.empty;
        }

        if(typeof this.state.push !== null){
            push = this.state.push;
        }

        this.setState({
            'content' : content,
            'push' : push,
            'data' : newData
        });
    },
    dropCallback : function(newData){
        var push = this.pushStates.idle;
        var content = this.contentStates.filled;

        if( ! this.hasData(newData)){
            content = this.contentStates.empty;
        }

        if(typeof this.state.push !== null){
            push = this.state.push;
        }

        this.setState({
            'content' : content,
            'push' : push,
            'data' : newData
        });
    },
    filledContent : function(){
        var rows = this.getRows();

        return (
            <div id="single_abm_fill">
                <div className="portlet-body">
                    <table className="striped table dataTable">
                        {rows}
                    </table>
                </div>
            </div>
        );
    },
    resolvPushUI : function(){
        var state = this.pushStates.idle;
        var ui = "";

        if(this.state !== null){
            if(typeof this.state.push !== 'undefined'){
                state = this.state.push;
            }
        }

        switch(state){
            case this.pushStates.idle:
                ui = this.pushIdleUI();
                break;
            case this.pushStates.sending:
                ui = this.pushSendingUI();
                break;
            case this.pushStates.failed:
                ui = this.pushFailedUI();
                break;
        }

        return ui;
    },
    pushButtonUI : function(){
        return (
            <div className="col-md-2">
                <div className="input-icon right">
                    <button ref={(ref) => this.pushButton = ref } onClick={this.push.bind(null,null)} className="btn blue-hoki" style={{ width : 100 + '%' }} id="list_btn"><i className="fa fa-plus"></i></button>
                </div>
            </div>
        );
    },
    pushSendingUI : function(){
        return (
            <div className="row">
                {this.pushInputUI()}

                <div className="col-md-2">
                    <div className="input-icon right">
                        <center>
                            <img src="/img/flipflop.gif" width="30px" height="30px" />
                        </center>
                    </div>
                </div>
            </div>
        );
    },
    pushFailedUI : function(){
        return (
            <div className="row">
                {this.pushInputUI()}
                {this.pushButtonUI()}
            </div>
        );
    },
    pushIdleUI : function(){
        return (
            <div className="row">
                {this.pushInputUI()}
                {this.pushButtonUI()}
            </div>
        );
    },
    resolvPushExtras : function(){
        var extras = {
            disabled : '',
            className : 'input-icon right'
        };

        if(this.state !== null){
            switch(this.state.push){
                case this.pushStates.idle:
                    break;
                case this.pushStates.sending:
                    extras.disabled = 'disabled'
                    break;
                case this.pushStates.failed:
                    extras.className += ' has-error'
                    extras.message = <span style={{ color:'#AF0000' }}>{this.state.message}</span>
                    break;
            }
        }

        return extras;
    },
    pushInputUI : function(){
        var extras = this.resolvPushExtras();

        return (
            <div className="col-md-10">
                <div className={extras.className}>
                    <i className="fa fa-edit"></i>
                    <input disabled={extras.disabled} ref={(ref) => this.input = ref } type="text" className="form-cascade-control form-control" id="single_abm_push_input"/>
                    {extras.message}
                </div>
            </div>
        );
    },
    render : function(){
        var content = this.resolvContent()
        var error = this.resolvError()
        var pushUI = this.resolvPushUI()

        return (
            <div className="row">
                <div className="col-md-12">
                    {error}
                    <div className="portlet light form-group">
                        <div className="portlet-title">
                            <div className="caption">
                                <span className="caption-subject bold uppercase"> {this.props.fieldLabel} </span>
                                <span><small>{this.props.title}</small></span>
                            </div>
                        </div>

                        {pushUI}

                        {content}
                    </div>
                </div>
            </div>
        )
    }
});

module.exports = SingleAbmTable
