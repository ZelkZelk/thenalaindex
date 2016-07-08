var tagCloud = require('tag-cloud');

var States = {
    empty : 0,
    done : 1,
    loading : 2
};

var UI = {
    title : function(){
        return (
            <h2 className='analysis-title'>PALABRAS NOTABLES</h2>
        );
    },
    done : function(react){
        var data = react.tagCloud;
        var title = UI.title();
        var tags = [];
        var cloud;

        for(var i in react.state.analysis){
            var blob = react.state.analysis[i];

            tags.push({
                tagName : blob.word,
                count : blob.f
            });
        }

        tagCloud.tagCloud(tags,function (err, data) {
            cloud = {
                __html : data
            };
        });

        return (
            <div className="col">
                {title}

                <div className='tag-cloud' dangerouslySetInnerHTML={cloud}></div>
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
            <span></span>
        );
    },
};

var WordCountAnalysis = React.createClass({
    propTypes : {
        analysis : React.PropTypes.array.isRequired
    },
    getInitialState : function(){
        var state = States.empty;

        if(this.props.analysis.length > 0){
            state = States.done;
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
        this.setState({
            state : States.done,
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

module.exports = WordCountAnalysis
