
front =
  cache: {}

  i: ->
    front.cache.win = $(window)
    front.cache.header = $('.header')
    front.handlers()

  handlers: ->
    front.scroll()
    $(window).scroll front.scroll

  scroll: ->

    st = front.cache.win.scrollTop()
    header = front.cache.header

    if st < 40 and header.hasClass 'small'
      header.removeClass('small').addClass 'large'

    if st > 40 and header.hasClass 'large'
      header.removeClass('large').addClass 'small'

    
