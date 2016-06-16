(function (factory) {
    if (typeof define === 'function' && define.amd) {
// AMD. Register as anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
// Node / CommonJS
        factory(require('jquery'));
    } else {
// Browser globals.
        factory(jQuery);
    }
})(function ($) {

    'use strict';
    var console = window.console || {log: function () {
        }};
    function CropAvatar($element) {
        this.$container = $element;
        this.$avatarView = this.$container.find('.avatar-view');
        this.$avatar = this.$avatarView.find('img');
        this.$avatarModal = this.$container.find('#avatar-modal');
        this.$loading = this.$container.find('.loading');
        this.$avatarForm = this.$avatarModal.find('.avatar-form');
        this.$avatarUpload = this.$avatarForm.find('.avatar-upload');
        this.$avatarSrc = this.$avatarForm.find('.avatar-src');
        this.$avatarData = this.$avatarForm.find('.avatar-data');
        this.$avatarInput = this.$avatarForm.find('.avatar-input');
        this.$avatarSave = this.$avatarForm.find('.avatar-save');
        this.$avatarBtns = this.$avatarForm.find('.avatar-btns');
        this.$avatarWrapper = this.$avatarModal.find('.avatar-wrapper');
        this.$avatarPreview = this.$avatarModal.find('.avatar-preview');
        this.init();
    }

    CropAvatar.prototype = {
        constructor: CropAvatar,
        support: {
            fileList: !!$('<input type="file">').prop('files'),
            blobURLs: !!window.URL && URL.createObjectURL,
            formData: !!window.FormData
        },
        init: function () {
            this.support.datauri = this.support.fileList && this.support.blobURLs;
            if (!this.support.formData) {
                this.initIframe();
            }

            this.initTooltip();
            this.initModal();
            this.addListener();
        },
        addListener: function () {
            this.$avatarView.on('click', $.proxy(this.click, this));
            this.$avatarInput.on('change', $.proxy(this.change, this));
            this.$avatarForm.on('submit', $.proxy(this.submit, this));
            this.$avatarBtns.on('click', $.proxy(this.rotate, this));
        },
        initTooltip: function () {
            this.$avatarView.tooltip({
                placement: 'bottom'
            });
        },
        initModal: function () {
            this.$avatarModal.modal({
                show: false
            });
        },
        initPreview: function () {
            var url = this.$avatar.attr('src');
            this.$avatarPreview.html('<img src="' + url + '">');
        },
        initIframe: function () {
            var target = 'upload-iframe-' + (new Date()).getTime();
            var $iframe = $('<iframe>').attr({
                name: target,
                src: ''
            });
            var _this = this;
            // Ready ifrmae
            $iframe.one('load', function () {

                // respond response
                $iframe.on('load', function () {
                    var data;
                    try {
                        data = $(this).contents().find('body').text();
                    } catch (e) {
                        console.log(e.message);
                    }

                    if (data) {
                        try {
                            data = $.parseJSON(data);
                        } catch (e) {
                            console.log(e.message);
                        }

                        _this.submitDone(data);
                    } else {
                        _this.submitFail('Image upload failed!');
                    }

                    _this.submitEnd();
                });
            });
            this.$iframe = $iframe;
            this.$avatarForm.attr('target', target).after($iframe.hide());
        },
        click: function () {
            this.$avatarModal.modal('show');
            this.initPreview();
        },
        change: function () {
            var files;
            var file;
            if (this.support.datauri) {
                files = this.$avatarInput.prop('files');
                if (files.length > 0) {
                    file = files[0];
                    if (this.isImageFile(file)) {
                        if (this.url) {
                            URL.revokeObjectURL(this.url); // Revoke the old one
                        }

                        this.url = URL.createObjectURL(file);
                        this.startCropper();
                    }
                }
            } else {
                file = this.$avatarInput.val();
                if (this.isImageFile(file)) {
                    this.syncUpload();
                }
            }
        },
        submit: function () {
            if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
                return false;
            }

            if (this.support.formData) {
                this.ajaxUpload();
                return false;
            }
        },
        rotate: function (e) {
            var data;
            if (this.active) {
                data = $(e.target).data();
                if (data.method) {
                    this.$img.cropper(data.method, data.option);
                }
            }
        },
        isImageFile: function (file) {
            if (file.type) {
                return /^image\/\w+$/.test(file.type);
            } else {
                return /\.(jpg|jpeg|png|gif)$/.test(file);
            }
        },
        startCropper: function () {
            var _this = this;
            if (this.active) {
                this.$img.cropper('replace', this.url);
            } else {
                this.$img = $('<img src="' + this.url + '">');
                this.$avatarWrapper.empty().html(this.$img);
                this.$img.cropper({
                    aspectRatio: 1,
                    preview: this.$avatarPreview.selector,
                    strict: false,
                    crop: function (e) {
                        var json = [
                            '{"x":' + e.x,
                            '"y":' + e.y,
                            '"height":' + e.height,
                            '"width":' + e.width,
                            '"rotate":' + e.rotate + '}'
                        ].join();
                        _this.$avatarData.val(json);
                    }
                });
                this.active = true;
            }

            this.$avatarModal.one('hidden.bs.modal', function () {
                _this.$avatarPreview.empty();
                _this.stopCropper();
            });
        },
        stopCropper: function () {
            if (this.active) {
                this.$img.cropper('destroy');
                this.$img.remove();
                this.active = false;
            }
        },
        ajaxUpload: function () {
            var url = rootURL + "account/editAvatar";
            var data = new FormData(this.$avatarForm[0]);
            var _this = this;
            $.ajax(url, {
                type: 'post',
                data: data,
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend: function (xhr) {
                    _this.submitStart(xhr);
                },
                success: function (data) {
                    _this.submitDone(data);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    _this.submitFail(XMLHttpRequest);
                },
                complete: function () {
                    _this.submitEnd();
                }
            });
        },
        syncUpload: function () {
            this.$avatarSave.click();
        },
        submitStart: function (xhr) {
            this.$loading.fadeIn();
            xhr.setRequestHeader("Authorization", "Bearer " + Cookies.getJSON("PTSpot").access_token);
        },
        submitDone: function (response) {
            console.log(response);

            if (response.success === false) {
                alert(response.errors);
            } else {
                //loadAvatar(Cookies.getJSON("status").userID, "profile_avatar", saveAvatar)

                var userID = Cookies.getJSON("status").userID;

                var sessionStorageKey = "avatar" + userID;

                try {
                    sessionStorage.removeItem(sessionStorageKey);
                }
                catch (e) {
                    console.log("Storage failed: " + e);
                }

                readBlobFromStorage(userID, "profile_avatar", "https://placehold.it/120x120");
            }
            

            $('#avatar-modal').modal('hide');
        },
        submitFail: function (xhr) {
            if (xhr.status === 401) {
                $('#avatar-modal').modal('hide');
                $('#loginModal').modal('show');
            }
        },
        submitEnd: function () {
            this.$loading.fadeOut();
        },
        cropDone: function () {
            this.$avatarForm.get(0).reset();
            this.$avatar.attr('src', this.url);
            this.stopCropper();
            this.$avatarModal.modal('hide');
        },
        alert: function (msg) {
            var $alert = [
                '<div class="alert alert-danger avatar-alert alert-dismissable">',
                '<button type="button" class="close" data-dismiss="alert">&times;</button>',
                msg,
                '</div>'
            ].join('');
            this.$avatarUpload.after($alert);
        }
    };
    $(function () {
        return new CropAvatar($('#crop-avatar'));
    });
});
