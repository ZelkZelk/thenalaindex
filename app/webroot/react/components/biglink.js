var BigLink = React.createClass({
  protTypes: {
    label : React.PropTypes.string.isRequired,
    icon : React.PropTypes.string.isRequired,
    click : React.PropTypes.string.isRequired,
    key : React.PropTypes.number.isRequired,
    id : React.PropTypes.number.isRequired,
    sublabel : React.PropTypes.string,
  },
  onClick : function(data,e){
    this.props.click(data,e);
  },
  render: function() {
    var sublabel = "";

    if(typeof this.props.sublabel !== 'undefined'){
      sublabel = (
        <div className="desc">{this.props.sublabel}</div>
      );
    }

    return (
      <div className="col-md-4 biglink">
        <div className="info-box  bg-warning  text-white">
          <a style={{cursor: 'pointer'}} onClick={this.onClick.bind(null,this.props)} className="dashboard-stat green-soft dashboard-stat-light">
            <div className="visual">
              <i className={this.props.icon}></i>
            </div>
            <div className="details">
              <div className="number links">
                {this.props.label}
              </div>

              {sublabel}
            </div>
          </a>
        </div>
      </div>
    );
  }
});

module.exports = BigLink;
