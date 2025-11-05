<style>
    @font-face {
        font-family: 'TT Norms Pro';
        src: url('{{ asset('fonts/TT Norms Pro Regular.otf') }}') format('opentype');
        font-weight: 400;
        font-style: normal;
    }

    p, li {
        font-family: 'TT Norms Pro', sans-serif;
        font-weight: 400;
        font-style: normal;
        font-size: 18px;
        leading-trim: NONE;
        line-height: 100%;
        letter-spacing: -1%;
        color: #173430;

    }
</style>
<p>
    {!! $faq->html_answer !!}
</p>
