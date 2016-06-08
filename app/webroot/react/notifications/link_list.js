var BigLink = require('./biglink.js');

var LinkList = React.createClass({
    propTypes: {
        links : React.PropTypes.array.isRequired
    },
    render: function() {
        return (
            <div className="row">
                <div className="col-md-12 bigsinglesport">
                    <div className="portlet box">
                        <div className="portlet-body">
                            <div className="row"> { this.props.links.map(function(link,i){
                                    return <BigLink id={link.id} key={link.id} icon={link.icon} click={link.click} label={link.label} sublabel={link.sublabel}/>
                                })
                            } </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    },
});

module.exports = LinkList
