var HistoryItem = require('../components/target_item.js');

var States = {
    empty : 1,
    done : 2
};

var UI = {
    empty : (
        <div>No hay Historiales Disponibles</div>
    ),
    done : function(data,props){
        var properties = props;
        var list = data;
        var older = 0;

        var rows = list.map(function(item,i){
            older = item.id;

            return <HistoryItem
                swapper={properties.swapper}
                id={item.id}
                key={item.id} />
        });

        var renderUI = (
            <div>
                <table>
                    <tbody>
                        {rows}
                    </tbody>
                </table>
            </div>
        );

        return renderUI;
    }
};

var HistoryList = React.createClass({
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
        var renderUI = UI.empty;
        return renderUI;
    },
    resolvDoneUI : function(){
        var renderUI = UI.done(this.state.list,this.props);
        return renderUI;
    }
});

module.exports = TargetList
