var UI = {
    get : function(react){
        var meta = react.props.meta;

        return (
            <div className="col">
                <div className="stat-row">
                    <div className="stat-label">Fecha Exploración</div>
                    <div className="stat-value">{react.getCreated()}</div>
                </div>

                <div className="stat-row">
                    <div className="stat-label">Tamaño</div>
                    <div className="stat-value">{meta.size} bytes</div>
                </div>

                <div className="stat-row">
                    <div className="stat-label">MIME</div>
                    <div className="stat-value">{meta.mime}</div>
                </div>

                <div className="stat-row">
                    <div className="stat-label">Checksum</div>
                    <div className="stat-value">{meta.checksum}</div>
                </div>

                <div className="stat-row">
                    <div className="stat-label">Hash</div>
                    <div className="stat-value">{meta.hash}</div>
                </div>
            </div>
        );
    }
};

var HTMLStats = React.createClass({
    propTypes : {
        meta : React.PropTypes.object.isRequired
    },
    getCreated : function(){
        var created = this.props.meta.created;
        var datetime = created.split(' ');
        var date = datetime[0].split('-');

        return date[2] + '/' + date[1] + '/' + date[0];
    },
    render: function() {
        var renderUI = this.resolvRenderUI();
        return renderUI;
    },
    resolvRenderUI : function(){
        var renderUI = UI.get(this);
        return renderUI;
    },
});

module.exports = HTMLStats
