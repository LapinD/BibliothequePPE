<script>

function GetCpt(Var)
{
	var Cpt = '<?php
				$Tri->GetCompteur() ;
				?>' ; 
	return Cpt;
}

function Refresh()
{
    document.getElementById('Tri').style.display='none';
    document.getElementById('Tri').style.display='';    
}

</script>
