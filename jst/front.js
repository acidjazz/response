var front;

front = {
  cache: {},
  i: function() {
    front.cache.win = $(window);
    front.cache.header = $('.header');
    return front.handlers();
  },
  handlers: function() {
    front.scroll();
    return $(window).scroll(front.scroll);
  },
  scroll: function() {
    var header, st;
    st = front.cache.win.scrollTop();
    header = front.cache.header;
    if (st < 40 && header.hasClass('small')) {
      header.removeClass('small').addClass('large');
    }
    if (st > 40 && header.hasClass('large')) {
      return header.removeClass('large').addClass('small');
    }
  }
};
