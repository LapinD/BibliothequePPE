$(document).ready(function()
{
            $('#ConnexionOnClick').click(function()
            {
                document.getElementById('ConnexionPopUp').style.display='block';
            });
            $('#ConnexionPopFormQuit').click(function()
            {
                document.getElementById('ConnexionPopUp').style.display='none';
            });
            $('.ImagePresentation').click(function()
            {
                document.getElementById('ResumeLivre1').style.display='';
            });

});

$(document).ready(function()
{
    $(".MasterListGenre").click(function()
    {
        ButtonClicked=(this.id);
        ButtonClicked=ButtonClicked.replace(/[^0-9]/g, '');
        GenreListLivre="#SMasterListGenre"+ButtonClicked ;
        $(GenreListLivre).toggle('slow');
    });
});



