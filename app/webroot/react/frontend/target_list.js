var TargetItem = require('./target_item.js');

var States = {
    empty : 1,
    done : 4
};

var UI = {
    empty : (
        <div id="error" className="module_wrapper">
            <h1 className="module_title">No hay Sitios Explorados aun</h1>
            <div className="row">
                <p>
                    Vuelve en otra ocasion.
                </p>
            </div>
        </div>
    ),
    done : function(data,props){
        var properties = props;
        var list = data;

        var rows = list.map(function(item,i){
            return <TargetItem
                swapper={properties.swapper}
                id={item.id}
                key={item.id}
                url={item.url}
                name={item.name}
                histories={item.histories}
                first_crawl={item.first_crawl}
                last_crawl={item.last_crawl} />
        });

        var renderUI = (
            <div id="target_list" className="module_wrapper">
                <h1 className="module_title">Sitios Disponibles</h1>
                <ul className="module_list">
                    {rows}
                </ul>
            </div>
        );

        return renderUI;
    }
};

var TargetList = React.createClass({
    propTypes : {
        list : React.PropTypes.array.isRequired,
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
        var renderUI = "<div>View not set... yet!</div>";

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
        var renderUI = UI.empty;
        return renderUI;
    },
    resolvDoneUI : function(){
        var renderUI = UI.done(this.state.list,this.props);
        return renderUI;
    }
});

module.exports = TargetList
