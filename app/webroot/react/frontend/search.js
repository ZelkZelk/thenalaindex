var SearchItem = require('./search_item.js');

var States = {
    empty : 1,
    done : 4
};

var UI = {
    resolvTitle : function(title,props){
        var title = (
            <h1 className="module_title">
                No se ha encontrado contenido<br/>
                <span className="subtitle">Buscando '{props.term}'</span>
            </h1>

        );

        return title;
    },
    empty : function(props){
        var title = UI.resolvTitle('No se ha encontrado contenido',props);

        var renderUI = (
            <div id="error" className="module_wrapper">
                {title}

                <div className="row">
                    <p>
                        Intenta con otros términos de búsqueda.
                    </p>
                </div>
            </div>
        );

        return renderUI;
    },
    done : function(data,props){
        var properties = props;
        var list = data;

        var rows = list.map(function(item,i){
            return <SearchItem
                swapper={properties.swapper} />
        });

        var title = UI.resolvTitle('Resultados de la Búsqueda',properties);

        var renderUI = (
            <div id="search_results" className="module_wrapper">
                {title}

                <ul className="module_list">
                    {rows}
                </ul>
            </div>
        );

        return renderUI;
    }
};

var Search = React.createClass({
    propTypes : {
        results : React.PropTypes.array.isRequired,
        term : React.PropTypes.string.isRequired,
        swapper : React.PropTypes.func.isRequired
    },
    getInitialState : function(){
        var list = [];

        if(typeof this.props.list !== 'undefined'){
            list = this.props.list
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
        var renderUI = UI.empty(this.props);
        return renderUI;
    },
    resolvDoneUI : function(){
        var renderUI = UI.done(this.state.list,this.props);
        return renderUI;
    }
});

module.exports = Search
