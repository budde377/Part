(function ($) {
    $.fn.AJAXUpdate = function (requestType, settings, callback) {
        var self = $(this).first();
        var id = self.attr('id');
        if (self.length == 0) {
            return;
        }
        if (requestType == undefined) {
            requestType = 'GET';
        }
        if (settings == undefined) {
            settings = {};
        }
        settings['type'] = requestType;
        settings['url'] = '#';
        settings['success'] = function (data) {
            var object = $('#' + id, data);
            console.log(data);
            var html = object.html();
            if (object.size()) {
                self.html(html);
                self.trigger('AJAXUpdated');
            }
            self.AJAXStopLoad();
            if ($.isFunction(callback)) {
                callback(object, $(data));
            }
        };
        settings['dataType'] = 'html';

        self.AJAXLoad();
        if (id != undefined) {
            $.ajax(settings);
        } else {
            self.parent().AJAXUpdate();
        }
    };

    $.fn.AJAXLoad = function () {
        var self = $(this).first();
        var container = $('<div class="AJAXLoadContainer"></div>');
        var selfWidth = self.outerWidth(true);
        var selfHeight = self.outerHeight(true);
        var loader = $('<div class="loader"></div>');
        self.addClass('loading');
        container.height(selfHeight);
        container.width(selfWidth);
        self.wrap(container);

        loader.hide();
        loader.insertBefore(self);
        loader.fadeIn(200);
        self.bind('AJAXStopLoad', function () {
            var self = $(this).first();
            if (self.hasClass('loading')) {
                self.siblings('.loader').remove();
                self.unwrap();
                self.removeClass('loading');
                $(this).unbind('AJAXStopLoad');

            }
        });

    };

    $.fn.AJAXStopLoad = function () {
        $(this).trigger('AJAXStopLoad');
    };


    $.fn.mapForm = function () {
        var self = $(this).first();

        var data = {};

        var inputs = $('*:input:not(:submit,:checkbox,select,option)', self);
        inputs.each(function () {
            data[$(this).attr('name')] = $(this).val();
        });

        var checkboxes = $('input:checkbox:checked,input:radio:checked', self);

        checkboxes.each(function () {
            var name = $(this).attr('name');
            var val = $(this).val();
            if (data[name] == undefined || !$.isArray(data[name])) {
                data[name] = [];
            }
            data[name].push(val);
        });

        var select = $('select option:selected');

        select.each(function () {
            var name = $(this).parent('select').attr('name');
            var val = $(this).val();


            if (data[name] == undefined || !$.isArray(data[name])) {
                data[name] = [];
            }

            data[name].push(val);

        });

        return data;

    };

    $.fn.submitForm = function (callback) {
        var self = $(this);
        if (self.size() > 1) {
            self.each(function () {
                $(this).submitForm();
            });
            return;
        }
        if (self.size() == 0 || self[0].tagName.toLowerCase() != 'form') {
            return;
        }
        if (self.attr('enctype') == 'multipart/form-data') {
            self.submit();
        }

        var method = self.attr('method');
        var action = self.attr('action');

        if (method == undefined) {
            method = 'GET';
        }

        if (action == undefined) {
            action = '#';
        }
        var data = self.mapForm();

        self.AJAXUpdate(method, {url:action, data:data}, callback);

    };

    $.fn.smartSubmit = function (callback) {
        $(this).submit(function () {
            $(this).submitForm(callback);
            return false;
        });

    };

    $.fn.confirmEvent = function (event, message, callbackTrue, callbackFalse, checkForConfirm) {
        var self = $(this).first();
        self.bind(event, function () {
            if (!$.isFunction(checkForConfirm) || checkForConfirm() == true) {
                if (confirm(message)) {
                    if ($.isFunction(callbackTrue)) {
                        return callbackTrue();
                    }
                } else {
                    if ($.isFunction(callbackFalse)) {
                        return callbackFalse();
                    }
                }
            } else {
                return false;
            }
        });
    };

    $.fn.confirmClick = function (message, callbackTrue, callbackFalse, checkForConfirm) {
        var self = $(this).first();
        return self.confirmEvent('click', message, callbackTrue, callbackFalse, checkForConfirm);
    };

    $.fn.smartSelectBox = function () {
        var self = $(this);
        if (self.size() > 1) {
            self.each(function () {
                $(this).smartSelectBox();
            });
        } else if (self.size() == 1 && self.prop('tagName').toLowerCase() == 'select') {
            var newSelect = $("<div class='smartSelectBox'></div>");
            var arrow = $('<div class="arrow"></div> ');
            var text = $('<div class="option"></div> ');
            var multiple = self.attr('multiple') != undefined;
            text.appendTo(newSelect);
            arrow.appendTo(newSelect);
            newSelect.insertAfter(self);
            newSelect.width(self.width());
            var optionsList = $('<div class="optionsList"></div>');
            optionsList.insertAfter(newSelect);
            optionsList.hide();
            self.bind('closeBox', function () {
                if (arrow.hasClass('expanded')) {
                    newSelect.siblings('.optionsList').hide();
                    arrow.removeClass('expanded');
                }
            });
            self.bind('openBox', function () {
                if (!arrow.hasClass('expanded')) {
                    optionsList.show();
                    optionsList.width(newSelect.width() + parseInt(newSelect.css('paddingRight')) + parseInt(newSelect.css('paddingLeft')));
                    arrow.addClass('expanded');
                }
            });

            text.setBoxText = function () {
                var t = '';
                var selectedOptions = $('.selected', optionsList);
                var value = [];
                selectedOptions.each(function () {
                    value.push($(self.children().get($(this).index())).attr('value'));
                });
                self.val(value);
                self.trigger('change');
                if (selectedOptions.size() > 0) {
                    t += selectedOptions.first().text();
                    if (selectedOptions.size() > 1) {
                        t += '..';
                    }
                }
                $(this).text(t);
            };
            $('option', self).each(function () {
                var option = $('<div class="option"></div>');

                option.bind('selectOption', function () {
                    $(this).addClass('selected');

                    text.setBoxText();

                });

                option.bind('deselectOption', function () {
                    $(this).removeClass('selected');
                    text.setBoxText();
                });

                option.toggleSelectOption = function () {
                    if ($(this).hasClass('selected')) {
                        if ($('.selected', optionsList).size() > 1) {
                            option.trigger('deselectOption');
                        }
                    } else {
                        if (!multiple) {
                            $('.selected', optionsList).trigger('deselectOption');
                            $(document).click();
                        }
                        option.trigger('selectOption');
                    }

                };
                if ($(this).is(':selected')) {
                    option.addClass('selected');
                }
                option.click(function (event) {
                    option.toggleSelectOption();
                    event.stopPropagation();
                });

                option.text($(this).text());
                option.appendTo(optionsList);
            });


            text.text($(':selected', self).text());

            self.hide();

            var disableCheck = function () {
                if (self.attr('disabled') != undefined) {
                    newSelect.addClass('disabled');
                } else {
                    newSelect.removeClass('disabled');
                    newSelect.bind('click.openCloseBox', function (event) {
                        var select = $('select');
                        if (arrow.hasClass('expanded')) {
                            select.trigger('closeBox');
                        } else {
                            self.trigger('openBox');
                            select.not(self).trigger('closeBox');
                        }
                        event.stopPropagation();
                    });
                    $(document).bind('click.openCloseBox', function () {
                        $('select').trigger('closeBox');
                    });
                }
            };
            self.bind('disable', function () {
                if (self.attr('disabled') == undefined) {
                    self.attr('disabled',true);
                    newSelect.unbind('click.openCloseBox');
                    $(document).unbind('click.openCloseBox');
                    disableCheck();
                }
            });
            self.bind('enable', function () {
                if (self.attr('disabled') != undefined) {
                    self.attr('disabled',false);
                    disableCheck();
                }
            });
            disableCheck();


        }

    };

    $.fn.makeTableCheckable = function () {
        var self = $(this);
        if (self.size() > 1) {
            self.each(function () {
                $(this).makeTableCheckable();
            });
        } else if (self.size() == 1 && self.prop('tagName').toLowerCase() == 'table') {
            var userListRow = $('tr', self);
            userListRow.each(function () {
                var checkBox = $('<input type="checkbox" />');
                var checkBoxContainer = $('<td class="select"></td>');
                if ($(this).hasClass('self') || $(this).hasClass('parent')) {
                    checkBox.attr('disabled', true);
                    checkBox.addClass('disabled');
                } else {
                    $(this).click(function () {
                        if (checkBox.is(':checked')) {
                            checkBox.attr('checked', false);
                        } else {
                            checkBox.attr('checked', true);
                        }
                    });
                }

                checkBox.appendTo(checkBoxContainer);
                checkBoxContainer.prependTo($(this));

            });
        }
    };

})(jQuery);

$(document).ready(function () {
    var top = $('#Top');
    var maxTop = top.outerHeight() - 20;
    var topVal = undefined;
    $(window).scroll(function () {
        var top = parseInt(top.css('top'));
        var newTop = $(document).scrollTop();
        if (top < maxTop) {
            topVal = Math.min(newTop, maxTop) * -1;
            top.css('top', topVal);
        }
    });
    top.hover(function () {
        top.stop().animate({top:0}, 300);
    }, function () {
        top.stop().animate({top:topVal}, 300);
    });

    $('select').smartSelectBox();
});