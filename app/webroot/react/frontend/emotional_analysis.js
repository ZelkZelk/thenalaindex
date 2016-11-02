var tagCloud = require('tag-cloud');

var States = {
    empty : 0,
    done : 1,
    loading : 2
};

var Messages = {
    positive_3 : 'Extremadamente Positivo',
    positive_2 : 'Positivo',
    positive_1 : 'Neutral con tendencia a Positivo',
    neutral : 'Totalmente Neutral',
    negative_1 : 'Neutral con tendencia a Negativo',
    negative_2 : 'Negativo',
    negative_3 : 'Extremadamente negativo',
};

var Tier = {
    positive_3 : 50,
    positive_2 : 20,
    positive_1 : 10,
    neutral : 0,
    negative_1 : -10,
    negative_2 : -20,
    negative_3 : -50,
};

var ScoreUI = {
    resolv : function(score){
        var message = this.resolvMessage(score);

        return (
            <span>{message}</span>
        );
    },
    resolvMessage : function(score){
        var message = "Sin calificar";

        if(score >= Tier.positive_3){
            message = Messages.positive_3;
        }
        else if(score >= Tier.positive_2){
            message = Messages.positive_2;
        }
        else if(score >= Tier.positive_1){
            message = Messages.positive_1;
        }
        else if(score >= Tier.neutral){
            message = Messages.neutral
        }
        else if(score >= Tier.negative_1){
            message = Messages.negative_1
        }
        else if(score >= Tier.negative_2){
            message = Messages.negative_2
        }
        else if(score >= Tier.negative_3){
            message = Messages.negative_3
        }

        return message;
    }
};

var UI = {
    title : function(){
        return (
            <h2 className='analysis-title'>ANÁLISIS EMOCIONAL</h2>
        );
    },
    done : function(react){
        return (
            <div className="col">
                {ScoreUI.resolv(react.state.analysis.score)}
            </div>
        );
    },
    empty : function(react){
        var title = UI.title();

        return (
            <div className="col">
                {title}
                <span className="analysis-row">Aún no se ha realizado el análisis</span>
            </div>
        );
    },
    loading : function(react){
        return (
            <span>Obteniendo info de análisis...</span>
        );
    },
};

var EmotionalAnalysis = React.createClass({
    propTypes : {
        analysis : React.PropTypes.object.isRequired
    },
    getInitialState : function(){
        var state = States.done;

        if(this.props.analysis.score === null){
            state = States.empty;
        }

        return {
            state : state,
            analysis : this.props.analysis
        };
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    loading : function(){
        this.setState({
            state : States.loading
        });
    },
    done : function(analysis){
        var state = States.done;

        if(analysis === null){
            state = States.empty;
        }

        this.setState({
            state : state,
            analysis : analysis
        });
    },
    resolvRenderUI : function(){
        var renderUI = ( <div> View not set yet! </div> );

        switch(this.state.state){
            case States.done:
                renderUI = UI.done(this);
                break;
            case States.empty:
                renderUI = UI.empty(this);
                break;
            case States.loading:
                renderUI = UI.loading(this);
                break;
        }

        return renderUI;
    },
});

module.exports = EmotionalAnalysis
