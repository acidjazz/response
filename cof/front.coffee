
front =
  cache: {}

  i: ->
    front.cache.win = $(window)
    front.cache.header = $('.header')
    front.handlers()

  handlers: ->
    $(window).scroll front.scroll

  scroll: ->

    st = front.cache.win.scrollTop()
    header = front.cache.header

    return true if st > 500

    if st < 40 and header.hasClass 'small'
      header.removeClass('small').addClass 'large'

    if st > 40 and header.hasClass 'large'
      header.removeClass('large').addClass 'small'

    
