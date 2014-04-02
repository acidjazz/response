_ =
  off: (el) ->
    i = 0
    len = arguments.length

    while i isnt len
      if arguments[i] instanceof jQuery
        arguments[i].removeClass("on").addClass "off"
      else
        $(arguments[i]).removeClass("on").addClass "off"
      i++
    return

  on: (el) ->
    i = 0
    len = arguments.length

    while i isnt len
      if arguments[i] instanceof jQuery
        arguments[i].removeClass("off").addClass "on"
      else
        $(arguments[i]).removeClass("off").addClass "on"
      i++
    return

  loader: (on_) ->
    if on_
      _.on ".loader", ".overlay"
      return true
    _.off ".loader", ".overlay"
    return

  status: (copy, timeout, loader) ->
    if copy
      if loader
        _.on ".overlay", ".status", ".loader"
      else
        _.on ".status"
        _.off ".overlay", ".loader"
      $(".status .copy").html copy
      setTimeout _.status, timeout * 1000  if timeout
      return true
    _.off ".overlay", ".status", ".loader"
    return

  encode: (str) ->
    return encodeURIComponent(str)
      .replace(/!/g, '%21')
      .replace(/'/g, '%27')
      .replace(/\(/g, '%28')
      .replace(/\)/g, '%29')
      .replace(/\*/g, '%2A')
      .replace(/%20/g, '+')

  t: (category, action, label, value) ->
    _gaq.push ['_trackEvent', category, action, label, value]

