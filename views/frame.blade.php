<script>
    @if(! $error)
        parent.closeFrame()
    @else
        parent.closeFrameWithError('{{ $error }}')
    @endif
</script>
