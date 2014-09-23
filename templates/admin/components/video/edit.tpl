{% import 'macro/notify.tpl' as notifytpl %}
<link href="{{ system.theme }}/css/datepicker.css" rel="stylesheet">
<script type="text/javascript" src="{{ system.theme }}/js/bootstrap-datepicker.js"></script>
<script src="{{ system.theme }}/js/maxlength.js"></script>
<link rel="stylesheet" href="{{ system.theme }}/css/fileupload/jquery.fileupload.css">
<script type="text/javascript" src="{{ system.script_url }}/resource/ckeditor/ckeditor.js"></script>
<script src="{{ system.script_url }}/resource/ckeditor/adapters/jquery.js"></script>
<script src="{{ system.theme }}/js/yandex-translate.js"></script>
<script type="text/javascript">
    $(document).ready(
            function()
            {
                // prepare ckeditor
                CKEDITOR.disableAutoInline = true;
                $('.wysi').ckeditor({language: '{{ system.lang }}'});
                $('.form-horizontal').submit(function() {
                    var is_fail = false;
                    $.ajax({
                        async: false,
                        type: 'GET',
                        url: ffcms_host + '/api.php?iface='+loader+'&object=checkauth',
                        success: function(data) {
                            if(data < 1) {
                                is_fail = true;
                            }
                        },
                        error: function() {
                            is_fail = true;
                        }
                    });
                    if(is_fail) {
                        if(!confirm('{{ language.admin_formsubmit_notify }}'))
                            return false;
                    }
                    window.onbeforeunload = null;
                });
                $('#datefield').datepicker();
                $('input[maxlength]').maxlength({alwaysShow: true});
            }
    );
    window.onbeforeunload = function (evt) {
        var message = "{{ language.admin_page_not_saved }}";
        if (typeof evt == "undefined") {
            evt = window.event;
        }
        if (evt) {
            evt.returnValue = message;
        }
        return message;
    }
    function videoPosterDelete(id) {
        $.get(ffcms_host+'/api.php?iface='+loader+'&object=videoposterdelete&type=2&id='+id, function(){
            $('#posterobject').remove();
        });
    }
    function parseVideoURL(url) {

        function getParm(url, base) {
            var re = new RegExp("(\\?|&)" + base + "\\=([^&]*)(&|$)");
            var matches = url.match(re);
            if (matches) {
                return(matches[2]);
            } else {
                return("");
            }
        }

        var retVal = {};
        var matches;

        if (url.indexOf("youtube.com/watch") != -1) {
            retVal.provider = "youtube";
            retVal.id = getParm(url, "v");
        } else if (matches = url.match(/vimeo.com\/(\d+)/)) {
            retVal.provider = "vimeo";
            retVal.id = matches[1];
        }
        return(retVal);
    }

    function prepareVideoCode() {
        var url = $('#video_data_url').val();
        var size = $("#videosize").val();
        var defsize = {
            1 : {
                'width' : 640,
                'height' : 360
            },
            2 : {
                'width' : 560,
                'height' : 315
            },
            3 : {
                'width' : 853,
                'height' : 480
            }
        };
        if(url.length < 1)
            return;
        if(size < 1 || size > 3)
            size = 1;
        var video = parseVideoURL(url);
        var response = null;
        if(video.provider == "youtube") {
            response = '<iframe width="'+defsize[size]['width']+'" height="'+defsize[size]['height']+'" src="//www.youtube.com/embed/'+video.id+'" frameborder="0" allowfullscreen></iframe>';
            $('#remoteposterlink').attr("href", 'http://img.youtube.com/vi/'+video.id+'/0.jpg');
        }
        if(video.provider == "vimeo") {
            response = '<iframe src="//player.vimeo.com/video/'+video.id+'?byline=0" width="'+defsize[size]['width']+'" height="'+defsize[size]['height']+'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
            $.ajax({
                type:'GET',
                url: 'http://vimeo.com/api/v2/video/' + video.id + '.json',
                jsonp: 'callback',
                cache: false,
                async: false,
                dataType: 'jsonp',
                success: function(data){
                    $('#remoteposterlink').attr("href", data[0].thumbnail_large);
                }
            });
        }

        if(response == null) {
            $('#hidden-error-video').removeClass('hidden');
            $('#hidden-notify-video').addClass('hidden');
        } else {
            $('#videocode').html(response);
            $('#hidden-notify-video').removeClass('hidden');
            $('#hidden-error-video').addClass('hidden');
            $('#remoteposter').removeClass('hidden');
        }
    }
    function translateVideo(lang_source, lang_target, api_key) {
        var title_source = $('#video_title_'+lang_source).val();
        var text_source = $('#textobject'+lang_source).val();
        if(text_source.length < 1)
            text_source = CKEDITOR.instances['textobject'+lang_source].getData();
        var desc_source = $('#video_desc_'+lang_source).val();
        var keywords_source = $('#keywords_'+lang_source).val();

        if(title_source.length > 0)
            translateText(lang_source, lang_target, title_source, api_key, 'video_title_', false);
        if(text_source.length > 0)
            translateText(lang_source, lang_target, text_source, api_key, 'textobject', true);
        if(desc_source.length > 0)
            translateText(lang_source, lang_target, desc_source, api_key, 'video_desc_', false);
        if(keywords_source.length > 0)
            translateText(lang_source, lang_target, keywords_source, api_key, 'keywords_', false);
    }
</script>
<h1>{{ extension.title }}<small>{{ language.admin_components_video_edit_title }}</small></h1>
<hr />
{% include 'components/video/menu_include.tpl' %}
{% if notify.nocode %}
    {{ notifytpl.error(language.admin_components_video_edit_notify_codeempty) }}
{% endif %}
{% if notify.notitle %}
    {{ notifytpl.error(language.admin_components_video_edit_notify_titleempty) }}
{% endif %}
{% if notify.nocat %}
    {{ notifytpl.error(language.admin_components_video_edit_notify_catempty) }}
{% endif %}
{% if notify.wrongway %}
    {{ notifytpl.error(language.admin_components_video_edit_notify_patherror) }}
{% endif %}
{% if notify.notext %}
    {{ notifytpl.error(language.admin_components_video_edit_notify_textempty) }}
{% endif %}
{% if notify.success %}
    {{ notifytpl.success(language.admin_components_video_edit_notify_success) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-6">
            <h2>{{ language.admin_components_video_edit_pathway_title }}</h2>

            <div class="input-group">
                <input class="form-control" type="text" id="out" name="pathway" value="{{ video.pathway }}" onkeyup="return pathCallback();">
                <span class="input-group-addon">.html</span>
            </div>
            <span class="help-block">{{ language.admin_components_video_edit_pathway_desc }}</span>
        </div>
        <div class="col-lg-6">
            <h2>{{ language.admin_components_video_edit_date_title }}<small><input type="checkbox" id="setcurrentdate" name="current_date"/> {{ language.admin_components_video_edit_date_current }}</small></h2>
            <input type="text" name="date" id="datefield" data-date-format="dd.mm.yyyy" value="{{ video.date }}" class="form-control" />
            <span class="help-block">{{ language.admin_components_video_edit_date_desc }}</span>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <h2>{{ language.admin_components_video_edit_code_title }}</h2>
            <ul class="nav nav-tabs" role="tablist">
                <li class="active"><a href="#">{{ language.admin_components_video_edit_code_tab_code }}</a></li>
                <li><a href="#" data-toggle="modal" data-target="#urlvideo">{{ language.admin_components_video_edit_code_tab_url }}</a></li>
            </ul>
            <textarea name="videocode" placeholder="&lt;iframe src=&quot;https://youtube.com/embed/....&gt;&lt;/iframe&gt;" class="form-control" id="videocode">{{ video.code }}</textarea>
            <span class="help-block">{{ language.admin_components_video_edit_code_desc }}</span>
        </div>
    </div>

    <p class="alert alert-info">{{ language.admin_components_video_edit_notify_languages }}</p>
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {% for itemlang in langs.all %}
                <li{% if itemlang == langs.current %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
                {% if itemlang != langs.current %}
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#" onclick="return translateVideo('{{ langs.current }}', '{{ itemlang }}', '{{ system.yandex_translate_key }}');">{{ language.admin_autotranslate }} <span class="label label-danger">{{ langs.current }}</span> -> <span class="label label-success">{{ itemlang }}</span></a></li>
                        </ul>
                    </li>
                {% endif %}
            {% endfor %}
        </ul>
        <div class="tab-content">
            {% for itemlang in langs.all %}
            <div class="tab-pane fade{% if itemlang == langs.current %} in active{% endif %}" id="{{ itemlang }}">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>{{ language.admin_components_video_edit_vname_title }}[{{ itemlang }}]</h2>
                        <input{% if itemlang == langs.current %} onkeyup="oJS.strNormalize(this)"{% endif %} type="text" name="title[{{ itemlang }}]" class="form-control" value="{{ video.title[itemlang] }}" maxlength="100" id="video_title_{{ itemlang }}" />
                        <span class="help-block">{{ language.admin_components_video_edit_vname_desc }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Описание видеоролика[{{ itemlang }}]</h2>
                        <textarea name="text[{{ itemlang }}]" id="textobject{{ itemlang }}" class="wysi form-control">{{ video.text[itemlang] }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <h2>{{ language.admin_components_video_edit_desc_title }}[{{ itemlang }}]</h2>
                        <input type="text" name="description[{{ itemlang }}]" class="form-control" value="{{ video.description[itemlang] }}" maxlength="250" id="video_desc_{{ itemlang }}" />
                        <span class="help-block">{{ language.admin_components_video_edit_desc_desc }}</span>
                    </div>
                    <div class="col-lg-6">
                        <h2>{{ language.admin_components_video_edit_keywords_title }}[{{ itemlang }}]</h2>
                        <input type="text" id="keywords_{{ itemlang }}" name="keywords[{{ itemlang }}]" class="form-control" value="{{ video.keywords[itemlang] }}" maxlength="200" />
                        <input class="btn btn-info pull-right" type="button" value="Авто" onClick="countKeywords('{{ itemlang }}')">
                        <span class="help-block">{{ language.admin_components_video_edit_keywords_desc }}</span>
                    </div>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <h2 id="postertitle">{{ language.admin_components_video_edit_poster_title }}</h2>
            {% if video.poster_path %}
                <p class="alert alert-success" id="posterobject"><i class="fa fa-picture-o"></i> {{ video.poster_name }}
                    <a href="#postertitle" data-toggle="modal" data-target="#posterview" class="label label-info" target="_blank">{{ language.admin_components_video_edit_poster_view }}</a>
                    <a href="#postertitle" onclick="return videoPosterDelete({{ video.id }});" class="label label-danger">{{ language.admin_components_video_edit_poster_del }}</a></p>
            {% endif %}
            <input type="file" name="videoimage">
            <span class="help-block">{{ language.admin_components_video_edit_poster_desc }}</span>
        </div>
        <div class="col-lg-6">
            <h2>Параметры видео</h2>
            <label class="checkbox">
                <input type="checkbox" name="display_content"{% if video.display == 1 %} checked{% endif %} /> {{ language.admin_components_video_edit_checkbox_display }}
            </label>
            <label class="checkbox">
                <input type="checkbox" name="important_content"{% if video.important == 1 %} checked{% endif %} /> {{ language.admin_components_video_edit_checkbox_important }}
            </label>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <h2>{{ language.admin_components_video_edit_category_title }}</h2>

            <div>
                <select name="category" size="5" class="form-control">
                    {% for cat in video.categorys %}
                        <option value="{{ cat.id }}"{% if cat.id == video.cat_id %} selected{% endif %}>{{ cat.name }}</option>
                    {% endfor %}
                </select>
            </div>
            <span class="help-block">{{ language.admin_components_video_edit_category_desc }}</span>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <input type="submit" name="save" value="{{ language.admin_components_video_edit_btn_save }}" class="btn btn-success btn-large"/>
        </div>
    </div>
</form>
{% if video.poster_path %}
    <div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" id="posterview" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ language.admin_components_video_edit_poster_preview_modal }}</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <!-- no browser cache :D -->
                        <script>
                            document.write('<img src="{{ system.script_url }}{{ video.poster_path }}?rnd='+Math.random()+'" class="img-responsive" />');
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endif %}
<div class="modal fade modalurlextract" tabindex="-1" role="dialog" id="urlvideo" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">{{ language.admin_components_video_edit_buildcode_title }}</h3>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <p>{{ language.admin_components_video_edit_buildcode_desc }}</p>
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">URL</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" placeholder="https://www.youtube.com/watch?v=Sk84Au4811A" id="video_data_url">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{ language.admin_components_video_edit_buildcode_size }}</label>
                            <div class="col-sm-10">
                                <select name="videosize" id="videosize" class="form-control">
                                    <option value="1" selected>640x360</option>
                                    <option value="2">560x315</option>
                                    <option value="3">853x480</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group hidden" id="remoteposter">
                            <label class="col-sm-2 control-label">{{ language.admin_components_video_edit_buildcode_service_poster }}</label>
                            <div class="col-sm-10"><a href="#" target="_blank" id="remoteposterlink">{{ language.admin_components_video_edit_buildcode_service_save }}</a></div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <a href="#" class="btn btn-default" onclick="return prepareVideoCode();">{{ language.admin_components_video_edit_buildcode_button_work }}</a>
                            </div>
                        </div>
                    </form>
                    <p class="hidden alert alert-success" id="hidden-notify-video">{{ language.admin_components_video_edit_buildcode_notify_success }} <button type="button" class="close" data-dismiss="modal" aria-hidden="true">{{ language.admin_components_video_edit_buildcode_button_close_modal }} &times;</button></p>
                    <p class="hidden alert alert-danger" id="hidden-error-video">{{ language.admin_components_video_edit_buildcode_notify_fail }} <button type="button" class="close" data-dismiss="modal" aria-hidden="true">{{ language.admin_components_video_edit_buildcode_button_close_modal }} &times;</button></p>
                </div>
            </div>
        </div>
    </div>
</div>