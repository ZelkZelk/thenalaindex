var SearchItem = require('./search_item.js');
var SearchLoader = require('./search_loader.js');
var Modules = require('./modules.js');
var Dispatcher = require('./dispatcher.js');

var States = {
    empty : 1,
    done : 4
};

var UI = {
    resolvTitle : function(title,props){
        var t = (
            <h1 className="module_title">
                {title}<br/>
                <span className="subtitle">Buscando '{props.term}'</span>
            </h1>

        );

        return t;
    },
    empty : function(react){
        var properties = react.props;
        var title = UI.resolvTitle('No se ha encontrado contenido',properties);
        var loader = UI.loader(react,true);

        var renderUI = (
            <div id="error" className="module_wrapper">
                {title}

                <div className="row">
                    <p>
                        Intenta con otros términos de búsqueda.
                    </p>
                </div>

                {loader}
            </div>
        );

        return renderUI;
    },
    loader : function(react,last){
        var properties = react.props;
        var callbackSwapper = react.swapper;
        var callbackUrlResolver = react.urlResolver;

        var searcher = <SearchLoader
                            last={last}
                            page={properties.page}
                            swapper={callbackSwapper}
                            urlResolver={callbackUrlResolver} />

        return searcher;
    },
    rows : function(react){
        var data = react.state.list;
        var properties = react.props;

        var rows = data.map(function(item,i){
            return <SearchItem
                key={i}
                item={item}
                swapper={properties.swapper} />
        });

        return rows;
    },
    done : function(react,last){
        var properties = react.props;
        var rows = UI.rows(react);
        var title = UI.resolvTitle('Resultados de la Búsqueda',properties);
        var searcher = UI.loader(react,last);
        var loader = UI.loader(react,last);

        var renderUI = (
            <div id="search_results" className="module_wrapper">
                {title}

                <ul className="module_list">
                    {rows}
                </ul>

                {loader}
            </div>
        );

        return renderUI;
    }
};

var Search = React.createClass({
    propTypes : {
        results : React.PropTypes.array.isRequired,
        term : React.PropTypes.string.isRequired,
        swapper : React.PropTypes.func.isRequired,
        page : React.PropTypes.number.isRequired
    },
    getInitialState : function(){
        var list = [];

        if(typeof this.props.results !== 'undefined'){
            list = this.props.results
        }

        return this.resolvState(list);
    },
    resolvState : function(list){
        var state;

        if(list.length > 0){
            state = States.done;
        }
        else{
            state = States.empty;
        }

        return {
            state : state,
            list : list
        };
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    getParams : function(){
        var term = this.props.term;
        return Modules.search.params(term);
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
    resolvRenderUI : function(){
        var renderUI = ( "<div>View not set... yet!</div>" );

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
        var renderUI = UI.done(this,false);
        return renderUI;
    }
});

module.exports = Search
