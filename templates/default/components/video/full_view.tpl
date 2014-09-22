<link rel="stylesheet" href="{{ system.script_url }}/resource/fancybox/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
<script type="text/javascript" src="{{ system.script_url }}/resource/fancybox/jquery.fancybox.pack.js?v=2.1.5"></script>
<script>
    $(document).ready(function() {
        $(".fancybox").fancybox({
            openEffect	: 'elastic',
            closeEffect	: 'elastic'
        });
    });
</script>

<article class="article-item" itemscope="itemscope" itemtype="http://schema.org/NewsArticle">
    <ol class="breadcrumb">
        <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
        <li><a href="{{ system.url }}/video/">{{ language.video_global_title }}</a></li>
        {% if local.category_url != null %}
            <li><a href="{{ system.url }}/video/{{ local.category_url }}">{{ local.category_name }}</a></li>
        {% endif %}
        <li class="active">{{ local.title[:50] }}{% if local.title|length > 50 %}...{% endif %}</li>
    </ol>
    <h1>{{ local.title }}</h1>
    <div class="meta">
        <span><i class="fa fa-list"></i><a href="{{ system.url }}/video/{{ local.category_url }}" itemprop="genre">{{ local.category_name }}</a></span>
        <span><i class="fa fa-calendar"></i><time datetime="{{ local.unixtime|date("c") }}" itemprop="datePublished">{{ local.date }}</time></span>
        <span><i class="fa fa-user"></i><a href="{{ system.url }}/user/id{{ local.author_id }}" itemprop="author">{{ local.author_nick }}</a></span>
        <span><i class="fa fa-eye"></i> {{ local.view_count }}</span>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="text-center">
                {{ local.code }}
            </div>
            <hr />
            <div class="row">
                <div class="col-md-3">
                    {% if local.poster %}
                        <img src="{{ local.poster }}" alt="{{ local.title }}" class="img-responsive img-thumbnail" />
                    {% else %}
                        <img src="{{ system.script_url }}/resource/cmscontent/video-empty.jpg" class="img-responsive img-thumbnail" alt="no poster" />
                    {% endif %}
                </div>
                <div class="col-md-9">
                    {{ local.text }}
                </div>
            </div>
        </div>
    </div>
    {% if local.similar_items %}
        <h3>{{ language.video_similar_item_title }}</h3>
        <div class="row">
            {% for similar in local.similar_items %}
                <div class="col-md-3">
                    <div class="thumbnail">
                        <h4><a href="{{ system.url }}/video/{{ similar.link }}">{{ similar.title[:15] }}</a></h4>
                        {% if similar.poster %}<img src="{{ similar.poster }}" alt="similar.title" class="img-responsive" />{% endif %}
                        <div class="caption">
                            <p>{{ similar.preview }}...</p>
                        </div>
                    </div>
                </div>
            {% endfor %}
        </div>
    {% endif %}
        <div class="meta">
            <span><i class="fa fa-tags"></i>
                {% for tag in local.tags %}
                    <a href="{{ system.url }}/video/tag/{{ tag }}.html">{{ tag }}</a>{% if not loop.last %},{% endif %}
                {% endfor %}
            </span>
        </div>
        <meta itemprop="keywords" content="{% for tag in local.tags %}{{ tag }}{% if not loop.last %},{% endif %}{% endfor %}">
</article>
{# include comment area #}
{% include 'modules/comments/comment_area.tpl' %}
