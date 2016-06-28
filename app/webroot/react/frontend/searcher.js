var States = {
    ready : 0,
    empty : 1,
    invalid : 2
}

var Classes = {
    empty : 'has-error',
    invalid : 'has-error',
    ready : ''
};

var Placeholders = {
    empty : 'No puede ir vacío!',
    ready : 'Ingrese término de búsqueda...',
    invalid : 'Solo letras y números!'
};

var UI = {
    getInput : function(react,state){
        var swapper = react.props.swapper;
        var placeholder = Placeholders[state];
        var className = Classes[state];

        var renderUI = (
            <input className={className} type="text" id="searcher" placeholder={placeholder} ref={ function(ref){
                if(ref === null){
                    return;
                }

                react.input = ref;
                react.input.onkeypress = react.keyPresser
            }} />
        );

        return renderUI;
    },
    ready : function(react){
        var renderUI = UI.getInput(react,'ready');
        return renderUI;
    },
    empty : function(react){
        var renderUI = UI.getInput(react,'empty');
        return renderUI;
    },
    invalid : function(react){
        var renderUI = UI.getInput(react,'invalid');
        return renderUI;
    },
};

var Search = React.createClass({
    input : null,
    module : 'search',
    propTypes : {
        swapper : React.PropTypes.func.isRequired
    },
    getModule : function(){
        return this.module;
    },
    getParams : function(){
        return {
            page : 1,
            term : this.trim(this.input.value)
        };
    },
    trim : function(str){
        if (!String.prototype.trim) {
            (function() {
                var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
                String.prototype.trim = function() {
                    return this.replace(rtrim, '');
                };
            })();
        }

        return str.trim();
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    getInitialState : function(){
        return {
            state : States.ready
        };
    },
    resolvRenderUI : function(){
        var renderUI = ( <div> View not set yet! </div> );

        switch(this.state.state){
            case States.ready:
                renderUI = UI.ready(this);
                break;
            case States.empty:
                renderUI = UI.empty(this);
                break;
            case States.invalid:
                renderUI = UI.invalid(this);
                break;
        }

        return renderUI;
    },
    keyPresser : function(event){
        if(event.keyCode === 13){
            this.submit();
            return false;
        }
        else{
            this.ready();
        }
    },
    empty : function(){
        this.input.value = '';

        if(this.state.state === States.empty){
            return;
        }

        this.setState({
            state : States.empty
        });
    },
    invalid : function(){
        this.input.value = '';

        if(this.state.state === States.invalid){
            return;
        }

        this.setState({
            state : States.invalid
        });
    },
    ready : function(){
        if(this.state.state === States.ready){
            return;
        }

        this.setState({
            state : States.ready
        });
    },
    submit : function(){
        if(this.isEmpty()){
            this.empty();
        }
        else if(this.isInvalid()){
            this.invalid();
        }
        else{
            this.send();
        }
    },
    isEmpty : function(){
        var value = this.input.value;

        if(value === ''){
            return true;
        }

        if(value.match(/^\s+$/)){
            return true;;
        }

        return false;
    },
    isInvalid : function(){
        var value = this.input.value;

        if(value.match(/^[áéíóúÁÉÍÓÚÑñA-Za-z0-9\s]+$/)){
            return false;
        }

        return true;
    },
    send : function(){
        this.props.swapper(this.getModule(),this.getParams());
        this.input.value = '';
        this.input.blur();
    }
});

module.exports = Search
