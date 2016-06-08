var Links = {
    blurMe : function(event){
        var source = event.target || event.srcElement;
        source.blur();
    }
};

module.exports = Links;
