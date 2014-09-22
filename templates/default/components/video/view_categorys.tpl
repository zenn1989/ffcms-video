<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
    <li><a href="{{ system.url }}/video/">{{ language.video_global_title }}</a></li>
    <li class="active">{{ language.video_category_breadcrumb }}</li>
</ol>
<h1>{{ language.video_category_title }}</h1>
<hr />
{% include 'components/video/menu_tabs.tpl' %}
{% for item in videocat %}
<div class="panel panel-info">
    <div class="panel-heading">
        <h2>{{ item.name }}</h2>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                {% if item.poster %}
                    <img class="img-responsive" src="{{ item.poster }}" alt="{{ item.name }}" />
                {% else %}
                    <img src="{{ system.script_url }}/resource/cmscontent/video-empty.jpg" class="img-responsive img-thumbnail" alt="no poster" />
                {% endif %}
            </div>
            <div class="col-md-9">
                <p>{{ item.desc }}</p>
                <a href="{{ system.url }}/video/{{ item.path }}" class="btn btn-info btn-block">{{ language.video_category_view }}</a>
            </div>
        </div>
    </div>
</div>
{% endfor %}