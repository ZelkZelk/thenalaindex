var HistoryLoader = require('./history_loader.js');
var HistoryItem = require('./history_item.js');
var Dispatcher = require('./dispatcher.js');
var Modules = require('./modules.js');

var States = {
    empty : 1,
    done : 2
};

var UI = {
    get : function(react,content,last){
        var callbackSwapper = react.swapper;
        var callbackUrlResolver = react.urlResolver;
        var state = react.state;
        var properties = react.props;
        var target = state.target;

        var renderUI = (
            <div id="history_list" className="module_wrapper">
                <h1 className="module_title">{target.name} <i>(pág. {state.page})</i></h1>

                {content}

                <HistoryLoader
                    last = {last}
                    page = {properties.page}
                    swapper={callbackSwapper}
                    urlResolver={callbackUrlResolver} />
            </div>
        );

        return renderUI;
    },
    empty  : function(react){
        var emptyUI = (
            <div className="row">
                <p>
                    No hay Historiales aun. Vuelve en otra ocasion.
                </p>
            </div>
        );

        var renderUI = UI.get(react,emptyUI,true);
        return renderUI;
    },
    done : function(react){
        var callbackSwapper = react.props.swapper;
        var state = react.state;
        var list = state.list;
        var target = state.target;

        var rows = list.map(function(item,i){
            return <HistoryItem
                id={item.id}
                key={item.id}
                index={item.index}
                starting={item.starting}
                ending={item.ending}
                http_petitions={item.http_petitions}
                css_crawled={item.css_crawled}
                html_crawled={item.html_crawled}
                js_crawled={item.js_crawled}
                img_crawled={item.img_crawled}
                hash={item.root_hash}
                target={target.name}
                target_id={target.id}
                swapper={callbackSwapper}/>
        });

        var content = (
            <ul className="module_list">
                {rows}
            </ul>
        );

        var renderUI = UI.get(react,content,false);
        return renderUI;
    }
};

var HistoryList = React.createClass({
    propTypes : {
        list : React.PropTypes.array.isRequired,
        swapper : React.PropTypes.func.isRequired,
        target : React.PropTypes.object.isRequired,
        page : React.PropTypes.number.isRequired,
    },
    getInitialState : function(){
        var list = [];
        var target = [];
        var page = 1;

        if(typeof this.props.list !== 'undefined'){
            list = this.props.list
        }

        if(typeof this.props.target !== 'undefined'){
            target = this.props.target
        }

        if(typeof this.props.page !== 'undefined'){
            page = this.props.page
        }

        return this.resolvState(list,target,page);
    },
    resolvState : function(list,target,page){
        var state;

        if(list.length > 0){
            state = States.done;
        }
        else{
            state = States.empty;
        }

        return {
            state : state,
            list : list,
            target : target,
            page : page
        };
    },
    getParams : function(){
        var id = this.state.target.id;
        var target = this.state.target.name;
        var page = this.state.page;

        return Modules.histories.params(id,target,page);
    },
    getPagedParams : function(params){
        var pagedParams = this.getParams();

        for(var i in params){
            pagedParams[i] = params[i];
        }

        return pagedParams;
    },
    urlResolver : function(module,updateParams){
        var params = this.getPagedParams(updateParams);
        var url = Dispatcher.resolvModuleUrl(module,params);
        return url;
    },
    swapper : function(module,updateParams){
        var params = this.getPagedParams(updateParams);
        Dispatcher.navigate(module,params,this.props.swapper);
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    resolvRenderUI : function(){
        var renderUI = ( <div>View not set... yet!</div> );

        switch(this.state.state){
            case States.empty:
                renderUI = this.resolvEmptyUI();
                break;
            case States.done:
                renderUI = this.resolvDoneUI();
                break;
        }

        return renderUI;
    },
    resolvEmptyUI : function(){
        var renderUI = UI.empty(this);
        return renderUI;
    },
    resolvDoneUI : function(){
        var renderUI = UI.done(this);
        return renderUI;
    }
});

module.exports = HistoryList
