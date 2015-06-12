$(document).ready(function()
        {
            $.getScript( "Var.js" ) ; 
            var MaxCpt = GetCpt(MaxCpt) ;
            var MidCpt = (Math.ceil(MaxCpt/2))-1 ;
            var Mincpt = 0 ;
            var i = 0 ;

            var CurrentPosition = 0 ;
            var SSMid = 'SlideShow' + MidCpt ;
            var DisplayLivreMid= 'ResumeLivre' + MidCpt;


            //Positionnement par défaut :

            document.getElementById(SSMid).style.position='absolute' ;
            document.getElementById(SSMid).style.right='50%' ;
            document.getElementById(SSMid).style.zIndex=MidCpt; 
            document.getElementById(DisplayLivreMid).style.display='block' ;

            
            //Positionnement Left : 
            var Size = 80;
            var InitSizeL = 80 ;
            CurrentPosition = 50 ;
            for(i = MidCpt-1 ; i >= Mincpt ; i--)
            {
                InitSizeL -= 5 ;
                var SSLeft = 'SlideShow' + i ;
                CurrentPosition += 10 ;
                document.getElementById(SSLeft).style.right=CurrentPosition+'%';
                document.getElementById(SSLeft).style.zIndex=i;
                document.getElementById(SSLeft).style.height=InitSizeL+'%';
            }

            //Positionnement Right : 
            CurrentPosition = 50;
            InitSizeR = 80 ;
            for(i = MidCpt+1 ; i < MaxCpt ; i++)
            {
                InitSizeR -= 5 ;
                var SSRight = 'SlideShow' + i ;
                CurrentPosition -= 10 ;
                document.getElementById(SSRight).style.right=CurrentPosition+'%';
                document.getElementById(SSRight).style.zIndex=i-i-i;
                document.getElementById(SSRight).style.height=InitSizeR+'%' ;
            }

            //Postionnement LeftClick :
            var CurrentPositionLeft=45;
            var CurrentPositionRight=55;
            var NbClick = MidCpt+1 ;
            var Middle = MidCpt ;
            var MidFocus = MaxCpt ;

            $('.Left').click(function()
            {   
                if(NbClick<MaxCpt)
                {
                    UseSizeR = Size ;
                    UseMidFocusR = MidFocus ;
                    for(i = Middle+1 ; i >= Mincpt ; i--)
                    {
                        $('#SlideShow'+i).animate(
                            {'z-index' : UseMidFocusR, 'height' : UseSizeR+"%"}, 10);
                        UseMidFocusR -= 1 ;
                        UseSizeR -=5 ;
                    }
                    
                    UseSizeL = Size ;
                    UseMidFocusL = MidFocus ;
                    for(i = Middle+1 ; i<= MaxCpt ; i++)
                    {
                        $('#SlideShow'+i).animate(
                            {'z-index' : UseMidFocusL, 'height' : UseSizeL+"%"}, 10) ;
                        UseMidFocusL -= 1 ;
                        UseSizeL -= 5 ;
                    }
                    Middle +=1 ;

                    ClickPositionLeft = CurrentPositionLeft;
                    for(i=MidCpt ; i>=Mincpt ; i--)
                    {
                        ClickPositionLeft += 10 ;
                        $('#SlideShow'+i).animate(
                        {'right': ClickPositionLeft +'%'}, 200);
                    }
                    CurrentPositionLeft = CurrentPositionLeft +10;

                    ClickPositionRight = CurrentPositionRight;
                    for(i=MidCpt+1; i<MaxCpt ; i++)
                    {
                        ClickPositionRight -=10 ;
                        $('#SlideShow'+i).animate(
                        {'right': ClickPositionRight +'%'}, 200);
                    }
                    CurrentPositionRight = CurrentPositionRight+10;

                    NbClick += 1 ;

                    for(i=0;i<MaxCpt;i++)
                    {
                        DisplayLivre='ResumeLivre' + i ;
                        document.getElementById(DisplayLivre).style.display='none' ;
                    }
                    DisplayLivre = 'ResumeLivre' + Middle ;
                    document.getElementById(DisplayLivre).style.display='block';
                }
            });
            
            $('.Right').click(function()
            {   
                if(NbClick>Mincpt+1)
                {

                    UseSizeR = Size ;
                    UseMidFocusR = MidFocus ;
                    for(i = Middle-1 ; i<= MaxCpt ; i++)
                    {
                        $('#SlideShow'+i).animate(
                            {'z-index' : UseMidFocusR, 'height' : UseSizeR+"%"}, 10);
                        UseMidFocusR -= 1 ;
                        UseSizeR-=5;
                    }
                    
                    UseSizeL = Size ;
                    UseMidFocusL = MidFocus ;
                    for(i = Middle-1 ; i >= Mincpt ; i--)
                    {
                        $('#SlideShow'+i).animate(
                            {'z-index' : UseMidFocusL, 'height' : UseSizeL+"%"}, 10) ;
                        UseMidFocusL -= 1 ;
                        UseSizeL -=5;
                    }
                    Middle -=1 ;

                    ClickPositionLeft = CurrentPositionLeft-10; 
                    for(i=MidCpt+1; i<MaxCpt ; i++)
                    {
                        ClickPositionLeft -= 10 ;
                        if(ClickPositionLeft<=0)
                        {
                            ClickPositionLeft = 0 ;
                        }
                        $('#SlideShow'+i).animate(
                        {'right': ClickPositionLeft +'%'}, 200);
                    }
                    CurrentPositionLeft = CurrentPositionLeft -10;

                    ClickPositionRight = CurrentPositionLeft-10;
                    for(i=MidCpt ; i>=Mincpt ; i--)
                    {
                        ClickPositionRight +=10 ;
                        if(ClickPositionRight>=100)
                        {
                            ClickPositionRight = 0 ;
                        }
                        $('#SlideShow'+i).animate(
                        {'right': ClickPositionRight +'%'}, 200);
                    }
                    CurrentPositionRight = CurrentPositionRight-10;


                    NbClick -= 1 ;

                    for(i=0;i<MaxCpt;i++)
                    {
                        DisplayLivre='ResumeLivre' + i ;
                        document.getElementById(DisplayLivre).style.display='none' ;
                    }
                    DisplayLivre = 'ResumeLivre' + Middle ;
                    document.getElementById(DisplayLivre).style.display='block';
                }
            });
        });

// BUTTON CLICK PAGE LIVRE 

$(document).ready(function()
{
    $("#ToggleResumeButton").click(function() {
    $("#AuteurResume").toggle('slow');
});
});