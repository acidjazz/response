var _;

_ = {
  off: function(el) {
    var i, len;
    i = 0;
    len = arguments.length;
    while (i !== len) {
      if (arguments[i] instanceof jQuery) {
        arguments[i].removeClass("on").addClass("off");
      } else {
        $(arguments[i]).removeClass("on").addClass("off");
      }
      i++;
    }
  },
  on: function(el) {
    var i, len;
    i = 0;
    len = arguments.length;
    while (i !== len) {
      if (arguments[i] instanceof jQuery) {
        arguments[i].removeClass("off").addClass("on");
      } else {
        $(arguments[i]).removeClass("off").addClass("on");
      }
      i++;
    }
  },
  loader: function(on_) {
    if (on_) {
      _.on(".loader", ".overlay");
      return true;
    }
    _.off(".loader", ".overlay");
  },
  status: function(copy, timeout, loader) {
    if (copy) {
      if (loader) {
        _.on(".overlay", ".status", ".loader");
      } else {
        _.on(".status");
        _.off(".overlay", ".loader");
      }
      $(".status .copy").html(copy);
      if (timeout) {
        setTimeout(_.status, timeout * 1000);
      }
      return true;
    }
    _.off(".overlay", ".status", ".loader");
  },
  encode: function(str) {
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
  },
  t: function(category, action, label, value) {
    return _gaq.push(['_trackEvent', category, action, label, value]);
  }
};
