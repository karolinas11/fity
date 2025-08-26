<button>POSALJI NOTIFIKACIJU</button>

<script>
    document.querySelector('button').addEventListener('click', function() {
        axios.post('/api/notification-test')
            .then(function (response) {
                console.log(response);
            })
            .catch(function (error) {
                console.log(error);
            });
    });
</script>
