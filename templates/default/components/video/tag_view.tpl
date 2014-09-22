<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
    <li><a href="{{ system.url }}/video/">{{ language.video_global_title }}</a></li>
    <li class="active">{{ language.video_tagitem_title }}</li>
</ol>
<h3>{{ language.video_tagitem_title }}: {{ local.tagname|escape }}</h3>
<hr />
<ul>
    {% for item in local.videoinfo %}
        <li><a href="{{ system.url }}/video/{{ item.link }}">{{ item.title }}</a></li>
    {% endfor %}
</ul>