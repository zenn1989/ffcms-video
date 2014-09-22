<script>
    $(document).ready(function(){
        $("[data-toggle='tooltip']").tooltip();
    });
</script>
{% if page_title|length > 0 %} {# for module news on main - have no title #}
    <ol class="breadcrumb">
        <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
        <li><a href="{{ system.url }}/video/">{{ language.video_global_title }}</a></li>
        {% if page_link != null %}
            <li class="active">{{ page_title }}</li>
        {% endif %}
    </ol>
    <h1>{{ page_title }}</h1>
    {% if page_desc|length > 0 %}
        <p>{{ page_desc }}</p>
    {% endif %}
    <hr />
{% endif %}
{% include 'components/video/menu_tabs.tpl' %}
<div class="row">
{% for videodata in local %}
        <div class="col-md-6">
            <article class="article-item" itemscope="itemscope" itemtype="http://schema.org/NewsArticle">
                <div class="thumbnail">
                    <h2 itemprop="name"><a href="{{ system.url }}/video/{{ videodata.full_video_uri }}" data-toggle="tooltip" data-placement="top" title="{{ videodata.title }}">{% if videodata.important > 0 %}<i class="fa fa-paperclip"></i> {% endif %}{{ videodata.title[:20] }}...</a></h2>
                    <div class="meta">
                        <span><i class="fa fa-list"></i><a href="{{ system.url }}/video/{{ videodata.category_url }}" itemprop="genre">{{ videodata.category_name }}</a></span>
                        <span><i class="fa fa-calendar"></i><time datetime="{{ videodata.unixtime|date("c") }}" itemprop="datePublished">{{ videodata.date }}</time></span>
                    </div>
                    {% if videodata.poster %}
                        <img alt="{{ videodata.title }}" src="{{ videodata.poster }}" class="img-responsive" style="display: table;margin: 0 auto;">
                    {% else %}
                        <img src="{{ system.script_url }}/resource/cmscontent/video-empty.jpg" class="img-responsive img-thumbnail" style="display: table;margin: 0 auto;" alt="no poster" />
                    {% endif %}

                    <div class="caption">
                        <p itemprop="text articleBody">{{ videodata.text }}</p>
                        <div class="meta">
                            {% if videodata.tags %}
                                <span><i class="fa fa-tags"></i>
                                    {% for tag in videodata.tags %}
                                        <a href="{{ system.url }}/video/tag/{{ tag }}.html">{{ tag }}</a>{% if not loop.last %},{% endif %}
                                    {% endfor %}
                                </span>
                                <meta itemprop="keywords" content="{% for tag in videodata.tags %}{{ tag }}{% if not loop.last %},{% endif %}{% endfor %}">
                            {% endif %}
                        </div>
                        <p class="text-center">
                            <a href="{{ system.url }}/video/{{ videodata.full_video_uri }}" itemprop="url" class="btn btn-primary"><i class="fa fa-eye"></i> {{ videodata.view_count }} {{ language.video_view_more }}</a>
                            <a href="{{ system.url }}/video/{{ videodata.full_video_uri }}#comment_load" class="btn btn-success"><i class="fa fa-comments"></i> {{ language.video_view_comments }}: <span itemprop="commentCount">{{ videodata.comment_count }}</span></a>
                        </p>
                    </div>
                </div>
            </article>
        </div>
    {% if loop.index%2 == 0 %}
    </div>
    <div class="row">
    {% endif %}
{% endfor %}
</div>
{{ pagination }}