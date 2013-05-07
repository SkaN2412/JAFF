$(function(){
    $("form").submit(function(){
        email = $(this['email'] ).val();
        password = $(this['password'] ).val();

        if ( email === "" || password === "" )
        {
            alert("Необходимо заполнить все поля!");
        }

        $.ajax({
            url: "?query=users.ajaxUser.auth",
            type: "post",
            data: { 'email': email, 'password': password },
            dataType: "json",
            success: function(data){
                document.location.href = document.location.href;
            }
        });
        return false;
    });
});