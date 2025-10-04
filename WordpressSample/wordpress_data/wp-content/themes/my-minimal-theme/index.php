<?php
/** 最小構成のテーマ：記事一覧を表示 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php bloginfo('name'); ?></title>
  <?php wp_head(); ?>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 0; padding: 24px; line-height: 1.8; }
    header, footer { text-align: center; opacity: .85; }
    .post { margin: 2.2rem 0; border-bottom: 1px solid #eee; padding-bottom: 1.6rem; }
    .post h2 { margin: 0 0 .6rem; font-size: 1.4rem; }
    .post .meta { font-size: .9rem; color: #666; margin-bottom: .6rem; }
    nav.pager a { margin-right: .8rem; }
  </style>
</head>
<body <?php body_class(); ?>>

<header>
  <h1><a href="<?php echo esc_url(home_url('/')); ?>" style="text-decoration:none;"><?php bloginfo('name'); ?></a></h1>
  <p><?php bloginfo('description'); ?></p>
</header>

<main>
<?php if ( have_posts() ) : ?>
  <?php while ( have_posts() ) : the_post(); ?>
    <article <?php post_class('post'); ?>>
      <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <div class="meta">
        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
        ・<?php the_category(', '); ?>
      </div>
      <div class="excerpt">
        <?php the_excerpt(); ?>
      </div>
    </article>
  <?php endwhile; ?>

  <nav class="pager">
    <span><?php previous_posts_link('← 新しい投稿'); ?></span>
    <span><?php next_posts_link('古い投稿 →'); ?></span>
  </nav>

<?php else : ?>
  <p>投稿がありません。</p>
<?php endif; ?>
</main>

<footer>
  <p>© <?php echo date('Y'); ?> <?php bloginfo('name'); ?></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
