@props(['seo'])

<script type="application/ld+json" nonce="{{ csp_nonce() }}">
{!! json_encode($seo->jsonLdArticle(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) !!}
</script>
