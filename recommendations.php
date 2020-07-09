// You need some test to see if the word is valid. 
// "is" should not be considered a valid match.
// This is a simple one based on length, a 
// "blacklist" would be better, but that's up to you.
function isValidEntry( $word )
{
    return strlen( $word ) >= 4;
}

//to hold all relevant search strings:
$terms = array();
$postTitleWords = explode( ' ' , strtolower( 'How to Make Coffee' ) );

for( $postTitleWords as $index => $word )
{
    if( isValidEntry( $word ) ) $terms[] = $word;
    else
    {
        $bef = @$postTitleWords[ $index - 1 ];
        if( $bef && !isValidEntry( $bef ) ) $terms[] = "$bef $word";
        $aft = @$postTitleWords[ $index + 1 ];
        if( $aft && !isValidEntry( $aft ) ) $terms[] = "$word $aft";
    }
}
$terms = array_unique( $terms );
if( !count( $terms ) ) 
{
    //This is a completely unique title!
}
$search = 'SELECT * FROM ENTRIES WHERE lower( TITLE ) LIKE \'%' . implode( '%\' OR lower( TITLE ) LIKE \'%' $terms ) . '\'%';
// either pump that through your mysql_search or PDO.
