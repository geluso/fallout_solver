<?php include "../common_bootstrap.php" ?>
<?php head("Optimal Fallout Hacking Solver") ?>

<style>
  pre {
    padding-left: 1em;
  }

  s {
    color: red;
  }

  .guesses em {
    color: green;
  }
</style>

<div class="item">
  <h1>How to Build A Fallout Hack Tool Engine</h1>

  <ol>
    <li> <a href="#intro">Introduction</a> </li>
    <li> <a href="#strategies">Strategies</a> </li>
    <li> <a href="#random">Random Guesses</a> </li>
    <li> <a href="#scoring">Similarity Scoring</a> </li>
    <li> <a href="#good">Good Choosing</a> </li>
    <li> <a href="#optimal">Optimal Choosing</a> </li>
  </ol>

  <a name="intro"></a>
  <h1>Introduction</h1>
  <p>
    The computer hacking minigame in Fallout is fun. It's logical. There's strategy
    to it. Given the choice between picking a lock and hacking a computer I'd take
    hacking every time. Bobby pins may break but the mind of the Lone Wanderer never
    splinters!!
  </p>
  <p>
    Hacking has always captured my imagination. What's the best way to solve the puzzle?
    How wold someone write a program to automatically solve it? Other people have created
    great tools. <a href="http://hackfallout.analogbit.com/">Tim's Optimal Fallout 3 Hack Tool</a>
    is my favorite. It has the most efficient input, the best feedback loop UX,
    and clearly highlights the best guess at every turn. Fantastic work Tim!
  </p>

  <p class="center">
    <a href="http://hackfallout.analogbit.com/" title="Tim's Optimal Fallout 3 Hack Tool">
      <img class="" src="tims_tool_75.png" alt="Tim's Optimal Fallout 3 Hack Tool"/>
    </a>
  </p>

  <p>
    The only way Tim's tool could be better would be if it were hooked up to a
    camera that automatically read everything on the screen so you never had to
    enter anything. I can't make something like that so someone else will have to
    come along and really finish these solvers off. Until that happens, we're already
    sitting as pretty as can be.
  </p>
    
  <p>
    Instead of creating another tool for others to use I'm just going to share my
    thoughts about creating a program that solves this puzzle. Let's explore how
    to build a Fallout Hack Tool engine!!
  </p>

  <a name="strategies"></a>
  <h1>Strategy</h1>
  <p>
    First we pick any random word. Each time we make a guess the system tells us
    how many letters in our guess match the right password. We can use this info
    to choose better guesses. Our next guess should have at least as many letters
    in common with our last guess as our last guess had in common with the correct
    password. We can eliminate all words that aren't as similar with our guess.
  </p>

  <p>
    Defining a term <b>similarity score</b> between two words makes it
    easier to talk about and compare guesses. Let's look at the similarity scores
    between a few words just to make the idea completely tangible.
  </p>

<p>
<pre>

FALLOUTFOUR           WANTING          WANTING          IDENTICAL
SPRINGROLLS           BANDITS          WASTING          IDENTICAL
===========           =======          =======          =========
00000000000 = 0/11    0110100 = 3/7    1101111 = 6/7    111111111 = 9/9

</pre>
</p>

  <h1>Playing Through A Round</h1>
  <p>
    Each column below represents one guess. The column shows the similarity score
    between the guess and the correct password, and the similarity score between
    each word and the guess. If a word has a lower similarity score to the guess
    than the guess has with the unknown password then that word is crossed off the list.
    Obviously, each guess is also crossed off the list because the system told us
    it wasn't right.
  </p>

<p>
<div class="row guesses">
<div class="col-xs-3">
<pre>
"BANDITS"
1/7 correct
===========
<s>BANDITS</s> 7/7
ENDINGS 1/7
WASTING 2/7
<s>CLEANSE</s> 0/7
MALTASE 1/7
DUSTERS 1/7
<s>CERTAIN</s> 0/7
MONSTER 1/7
FERTILE 1/7
STATION 1/7
SEEKING 1/7
PARTIES 3/7
WANTING 3/7
<s>GROUPED</s> 0/7
WAITING 2/7
LETTING 1/7
STATING 1/7
GAINING 2/7
</pre>
</div>

<div class="col-xs-3">
<pre>
"ENDINGS"
0/7 correct
===========
<s>BANDITS</s>
<s>ENDINGS</s> 7/7
WASTING 0/7
<s>CLEANSE</s>
MALTASE 0/7
<s>DUSTERS</s> 1/7
<s>CERTAIN</s>
MONSTER 0/7
FERTILE 0/7
STATION 0/7
SEEKING 0/7
<s>PARTIES</s> 1/7
WANTING 0/7
<s>GROUPED</s>
WAITING 0/7
LETTING 0/7
STATING 0/7
GAINING 0/7
</pre>
</div>

<div class="col-xs-3">
<pre>
"WASTING"
4/7 correct
===========
<s>BANDITS</s>
<s>ENDINGS</s>
<s>WASTING</s> 7/7
<s>CLEANSE</s>
<s>MALTASE</s> 2/7
<s>DUSTERS</s>
<s>CERTAIN</s>
<s>MONSTER</s> 0/7
<s>FERTILE</s> 2/7
<s>STATION</s> 2/7
<s>SEEKING</s> 2/7
<s>PARTIES</s>
WANTING 6/7
<s>GROUPED</s>
WAITING 6/7
LETTING 4/7
STATING 4/7
GAINING 4/7
</pre>
</div>

      <div class="col-xs-3">
<pre>
"LETTING"
4/7 correct
===========
<s>BANDITS</s>
<s>ENDINGS</s>
<s>WASTING</s>
<s>CLEANSE</s>
<s>MALTASE</s>
<s>DUSTERS</s>
<s>CERTAIN</s>
<s>MONSTER</s>
<s>FERTILE</s>
<s>STATION</s>
<s>SEEKING</s>
<s>PARTIES</s>
WANTING 4/7
<s>GROUPED</s>
WAITING 4/7
<s>LETTING</s> 7/7
STATING 4/7
<s>GAINING</s> 3/7
</pre>
      </div>

    </div>
  </p>

  <p>
    Darn. I lost that time. There was some slow progress at first. BANDITS and ENDINGS
    knocked out some words, but not enough. WASTING seemed to knock a lot of words out
    but it was too late in the game to narrow the choices down to a certain correct
    answer.
  </p>

  <h1>Completely Incorrect Guesses</h1>
  <p>
    It's worth mentioning that guessing ENDINGS brought up a special case. The system
    said ENDINGS had zero letters in common with the correct answer. I naturally
    realized that any word having any letters in common with ENDINGS must also not
    be the correct answer so I crossed those off the list.
  </p>

  <p>
    This is important to point out because it's something humans are very good at
    understanding intuitively but may require additional programming. This realization
    challenged my assumption that a good algorithm would leave any word with a similarity
    score greater than the similarity score reported by the system for the last guess.
  </p>

  <ul>
    <li>eliminate words with a lower similarity to the guess compared to the system.</li>
    <li>
      if the similarity of the guess is zero then eliminate words that have any
      similarity to the current guess.
    </li>
  </ul>

  <h1>Picking Better Guesses</h1>
  <p>
    The problem with the last round was the guesses didn't seem to always be the best
    choice. Perhaps some guesses are better than others. Here's a toy list of words
    that will help explore the problem. Additionally, imagine the game has progressed
    far enough so there are only two guesses left. What's the best guess here?
  </p>

  <pre>
  SOLDIER
  BICYCLE
  WANTING
  WASTING
  WARNING
  </pre>

  <p>
    SOLDIER and BICYCLE are awfully unique compared to every other word.
    WANTING, WASTING and WARNING are all pretty close to each other. 
    This situation sets up three logical groups of words to pick from.
  </p>

  <p>
    Remember, there are only two guesses left in this situation.
  </p>

  <pre>
  Group 1: [SOLDIER]
  Group 2: [BICYCLE]
  Group 3: [WANTING, WASTING, WARNING]
  </pre>

  <p>
    Guessing SOLDIER doesn't necessarily yield much information.
    If we guess SOLDIER and SOLDIER is correct, then we're simply lucky. If we guess
    SOLDIER and SOLDIER is incorrect here's what the similarity score would look like.
    SOLDIER has zero similarity with every other word.
  </p>

  <pre>
    SOLDIER       SOLDIER         SOLDIER         SOLDIER
    BICYCLE       WANTING         WASTING         WARNING
    =======       =======         =======         =======
    0000000 0/7   0000000 = 0/7   0000000 = 0/7   0000000 = 0/7
  </pre>

  <p>
    Picking SOLDIER has huge consequences if it's wrong. It leaves us with one
    guess left without gaining new information. BICYCLE is an equally bad place
    to start.
  </p>

  <pre>
    BICYCLE       BICYCLE         BICYCLE         BICYCLE
    SOLDIER       WANTING         WASTING         WARNING
    =======       =======         =======         =======
    0000000 0/7   0000000 = 0/7   0000000 = 0/7   0000000 = 0/7
  </pre>

  <p>
    Since neither SOLDIER nor BICYCLE gain information if they're incorrect
    the last guess is left as a crapshoot between four words. A 1/4 chance at
    guessing correctly.
  </p>

  <p>
    It turns out that picking from [WANTING, WASTING, WARNING] is a much
    better choice. Picking from this group of words yields three scenarios:
  </p>

  <pre>
  1. the guess is incorrect and the similarity is 0/7
  2. the guess is incorrect and the similarity is 6/7
  3. the guess is just plain correct
  </pre>

  <p>
    If the guess is correct with similarity 0/7 that leaves a crapshoot choice
    between SOLDIER and BICYCLE. A 1/2 chance.
    If the guess is correct with similarity 6/7 that leaves a crapshoot choice
    between the other two WA*TING words. Still a 1/2 chance.
    Both 1/2 chances are clearly better than the 1/4 chance left by chosing
    SOLDIER or BICYCLE first.
  </p>

  <p>
    There may not be a way to pick guesses that will guarantee a guess will
    eventually be correct in a certain number of tries, but it is clear that
    some guesses are better than others.
  </p>

  <p>
    Let's see how to write a program that ensures we pick the best guess every time.
  </p>

  <a name="optimal"></a>
  <h1>Optimal Choosing</h1>
</div>

<?php foot() ?>
