activateSlide = true;
var timer = null;
slideHolder = null;
slideContainer = null;
slidePlaying = false;
slideNav = null;
timeout = 3000;


function getSide(link) {
    ret = false;
    link = link.split("?");
    link = link[1].split('#');
    link = link[0].split('&');
    $.each(link, function (index, value) {
        req = value.split('=');
        retnext = false;
        $.each(req, function (index, value) {
            if (retnext) {
                ret = value;
            }
            if (value == 'side')
                retnext = true;
        });
    });
    return ret;
}

function saveText(input, link, id, tags) {
    link.text('');
    load = $('<div class="load"></div>');
    load.hide();
    load.prependTo(link.parent());
    load.fadeIn(200);
    text = input.val();
    closeTags(tags);
    $.get(url + '&ajax=edittext&id=' + id + '&text=' + text, function (data) {
        load.fadeOut(200, function () {
            load.remove();
            link.html(data);
        });

    });

}

function runningSlides() {
    if (activateSlide) {
        slideNext(slideHolder, slideContainer, slideNav);
    }

}

function slideNext(holder, container, navigate) {
    active = container.find('.active');
    nextactive = active.next();

    activenav = navigate.find('.active');
    nextactivenav = activenav.next();

    activateSlide = false;
    if (nextactive.size()) {

        newLeft = parsePx(holder.css('left')) - container.width();
        holder.animate({
            'left':newLeft + 'px'
        }, 'slow', function () {
            activateSlide = true;
        });

    } else {
        holder.animate({
            'left':'0px'
        }, 'slow', function () {
            activateSlide = true;
        });
        nextactive = holder.children('div').first();
        nextactivenav = navigate.children('li:not(.prec)').first();
    }

    active.removeClass('active');
    nextactive.addClass('active');
    activenav.removeClass('active');
    nextactivenav.addClass('active');

}

function slideGoTo(holder, container, navigate, to) {
    restartSlide();
    var active = holder.find('.active');
    var nextactive = holder.children();
    var i = 0;

    var activenav = navigate.find('.active');
    var nextactivenav = navigate.children(':not(.prec)');

    if (to < 1) {
        to = 1;
    }

    if (nextactive.size() < to) {
        to = nextactive.size();
    }
    tto = to - 1;

    nextactivenav = nextactivenav[tto];
    nextactive = nextactive[tto];

    activateSlide = false;

    newLeft = -container.width() * (tto);

    holder.animate({
        'left':newLeft + 'px'
    }, 'slow', function () {
        activateSlide = true;
    });

    activenav.removeClass('active');
    $(nextactivenav).addClass('active');
    active.removeClass('active');
    $(nextactive).addClass('active');

}


$(document).ready(function () {
    slideContainer = $('#SlideImage');
    slideImages = slideContainer.children('img');
    slideImages.wrapAll('<div id="SlideHolder"></div>');
    slideHolder = $('#SlideHolder');
    slideImages.wrap('<div class="holders"></div>');
    slideImages = slideContainer.find('.holders');
    slideNav = $('#SlideNav');
    i = accwidth = nextX = nextY = 0;
    slideImages.each(function () {
        i++;
        $(this).css('top', nextY + 'px');
        $(this).css('left', nextX + 'px');
        iitem = $("<li></li>");
        iitem.text(i);
        if (i == 1) {
            iitem.addClass('active');
            $(this).addClass('active');
        }

        iitem.appendTo(slideNav);
    });
    slideHolder.width(i * slideContainer.width());
    slideHolder.css('cursor', 'pointer');
    startSlide();
    slideHolder.click(function () {
        restartSlide();
        if (activateSlide) {
            slideNext(slideHolder, slideContainer, slideNav);
        }

    })
    navChildren = slideNav.children(':not(.prec)');
    navChildren.click(function (e) {
        if (activateSlide) {
            slideGoTo(slideHolder, slideContainer, slideNav, $(this).text());
        }

    });
    navChildren.css('cursor', 'pointer');

    tagContainer = $('#Tags');


    taglinks = tagContainer.find('a.link');
    tagLi = tagContainer.children();

    if (tagContainer.hasClass('editable')) {
        editTagsText(tagContainer, taglinks);
    } else {

        tagLi.css('cursor', 'pointer');

        tagLi.click(function () {
            $(this).find('a.link').trigger('click');

        });
        taglinks.click(function (e) {
            e.stopPropagation();
            $(location).attr('href', $(this).attr('href'));

            return true;
        });
    }

    editSlide = $('#EditSlide');
    if (editSlide.size()) {

        editSlideForm = editSlide.find('form');
        editSlideUpload = editSlideForm.find('.upload input');
        editSlideUploadParent = editSlideUpload.parent();
        autoSubmit(editSlideForm, editSlideUpload, editSlideUploadParent);
        editSlideForm.find('.submit').remove();
        editSlideLink = $('<span href="#" class="link closed">TilfÃ¸j</span>');
        editSlideLink.appendTo(editSlide);
        if (!editSlide.find('.error,.notion').size())
            editSlideForm.hide();
        editSlideLink.click(function () {
            editSlideLink.toggleClass('closed');
            if (editSlideLink.hasClass('closed')) {
                editSlideLink.text('TilfÃ¸j');
            } else {
                editSlideLink.text('Luk');
            }
            editSlideForm.slideToggle(300, "swing");
            return false;
        });

        playBar = $('<div class="playBar"></div>');
        pauseButton = $('<div class="pause playpause" title="Stop Diasshow"></div>');
        deleteButton = $('<div class="delete playpause" title="Slet Billede"></div>');
        pauseButton.appendTo(playBar);
        deleteButton.appendTo(playBar);
        playBar.appendTo(slideContainer);

        deleteButton.click(function () {
            stopSlide();
            activeImages = slideHolder.find('.holders.active img');
            if (confirm('Er du sikker pÃ¥ at du vil slette dette billede?')) {
                $(this).css('background-image', 'url(_img/load2.gif)');
                deleteImage(activeImages);
            } else {
                startSlide();
            }
        });

        pauseButton.click(function () {
            pauseSlideButton(pauseButton, playBar);
        });
    }
});

function deleteImage(image) {
    $.get(url + '&ajax=deleteImage&image=' + image.attr('src'), function (data) {
        if (data == 1) {
            $(location).attr('href', url);
        }
        startSlide();
    });
}

function restartSlide() {
    stopSlide();
    startSlide();
}

function stopSlide() {
    if (slidePlaying) {
        slidePlaying = false;
        clearInterval(timer);
    }
}

function startSlide() {
    if (!slidePlaying) {
        timer = setInterval("runningSlides()", timeout);
        slidePlaying = true;
    }
}

function pauseSlideButton(button, playBar) {
    stopSlide();
    button.removeClass('pause');
    button.addClass('play');
    button.attr('title', 'Start Diasshow');
    button.unbind('click');
    button.click(function () {
        playSlideButton(button, playBar);
    });
}

function playSlideButton(button, playBar) {
    runningSlides();
    startSlide();
    button.removeClass('play');
    button.addClass('pause');
    button.attr('title', 'Stop Diasshow');
    button.unbind('click');
    button.click(function () {
        pauseSlideButton(button, playBar);
    });
}

function editTagsText(tagContainer, taglinks) {
    taglinks.click(function (e) {
        e.preventDefault();

        e.stopPropagation();


        parent = $(this).parent();
        closeTags(tagContainer);

        parent.addClass('edit');
        input = $('<input type="text" />');
        submit = $('<input type="submit" class="submit" value="Gem"/>');
        link = $(this);

        input.bind('saveText', function () {
            id = getSide(link.attr('href'));
            if (id) {
                saveText(input, link, id, tagContainer);
            }
        });

        submit.click(function () {
            e.stopPropagation();

            input.trigger('saveText');
            return false;
        });

        input.keydown(function (event) {
            if (event.keyCode == 13) {

                input.trigger('saveText');
            }
        });

        $(document).keydown(function (event) {
            if (event.keyCode == 27) {
                closeTags(tagContainer);
            }
        })

        submit.appendTo(parent);
        input.appendTo(parent);
        input.val($(this).text());
        link.hide();
        input.focus();
        return false;
    });
}

function closeTags(tagContainer) {

    tagContainer.find('li.edit input[type=text]').unbind('focusout');
    tagContainer.find('li.edit input').remove();
    tagContainer.find('li.edit a.link').show();
    tagContainer.find('li.edit').removeClass('edit');
}