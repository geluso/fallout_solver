#!/usr/local/bin/python

import random
import argparse
from collections import defaultdict

DEFAULT_MIN_SIMILARITY_SCORE = 3

parser = argparse.ArgumentParser(description="Fallout computer hack utility. Plays games, shows similarity stats between words and solves given puzzles.")
parser.add_argument("--stat", action="store_true", help="Shows stats about words. Produces a grid showing similarity scores between each pair of words.")
parser.add_argument("--solve", action="store_true", help="Solves the puzzle with default words.")
parser.add_argument("--knockout", action="store_true", help="Shows knockout table.")
parser.add_argument("--depth", type=int, default=DEFAULT_MIN_SIMILARITY_SCORE, help="Minimum similarity score to show in stat table.")

choices = "cleanse,grouped,gaining,wasting,dusters,letting,endings,fertile,seeking,certain,bandits,stating,wanting,parties,waiting,station,maltase,monster"
choices = choices.upper()
choices = choices.split(",")

# make a copy of the list before it's suffled so
# we have something consistant to work with for stats
constant_choices = choices[:]
constant_choices.sort()

# shuffle the other list so the choices are srambled each time.
random.shuffle(choices)

def grid_similarity(min_similarity):
  key = "ABCDEFGHIJKLMNOPQR" 
  for i, choice in enumerate(constant_choices):
    print "%s %s" % (key[i], choice)
  print

  print "  A B C D E F G H I J K L M N O P Q R"
  for i, c1 in enumerate(constant_choices):
    line = "%s" % key[i]
    for c2 in constant_choices:
      score = similarity(c1, c2)
      if score == 0:
        symbol = "-"
      elif score == len(c1):
        symbol = "="
      elif score > min_similarity:
        symbol = str(score)
      else:
        symbol = "."

      line += " %s" % symbol
    print line
  print

def get_knockout(cc):
  # the knockout score represents the number of other choices knocked out
  # using information from one guess. Consider the four choices given below.
  #
  # DEAD
  # FEAR
  # DOOR
  # BAIT
  #
  # If we guess DEAD and the answer was FEAR we are told 2 characters are correct.
  # Compare the similarity of DEAD to every other choice. If we know DEAD has a
  # similarity of 2 with the correct against, we would be fools to make our second
  # guess have less than 2 similar characters to DEAD itself.
  # 
  # sim(DEAD, DEAD) = 4
  # sim(DEAD, FEAR) = 2
  # sim(DEAD, DOOR) = 1
  # sim(DEAD, BAIT) = 0

  knockout_score = defaultdict(list)

  # iterate over each guess
  for guess in cc:
    # iterate over all other choices as if they were the correct answer
    for possible_answer in cc:
      answer_score = similarity(guess, possible_answer)
      # calculate the scores of every other choice as if it were the next guess
      for next_guess in cc:
        next_guess_score = similarity(guess, next_guess)
        # how many other choices have similarity exactly equal to the current guess.
        if next_guess_score == answer_score:
          knockout_score[guess].append(next_guess)
  return knockout_score

def show_knockout(cc):
  knockout = get_knockout(cc)
  for guess in knockout:
    print guess, len(knockout[guess])

def show_choices():
  for choice in choices:
    print choice

def similarity(guess, answer):
  similar = 0
  for c1, c2 in zip(guess, answer):
    if c1 == c2:
      similar += 1
  return similar

def play_game():
  answer = random.choice(choices)
  guess = None
  attempts = 4

  show_choices()

  while guess != answer and attempts > 0:
    guess = raw_input(">").upper()
    attempts -= 1
    
    if guess not in choices:
      print ">Incorrect input."

    if guess == answer:
      break

    if guess != answer:
      print ">Entry denied"
      print ">%d/%d correct." % (similarity(guess, answer), len(answer))

    if attempts == 1:
      print ">1 attempt left"
    else:
      print ">%d attempts left" % (attempts)

  if attempts == 0:
    print ">Lockout in progress."
  elif guess == answer:
    print ">Exact match!"
    print "Please wait"
    print "while system"
    print "is accessed."

def main():
  args = parser.parse_args()
  if args.stat:
    grid_similarity(args.depth)
  elif args.knockout:
    cc = choices
    show_knockout(cc)

    while True:
      print
      our_guess = raw_input("you guessed:").upper()

      if our_guess == "Q":
        import sys
        sys.exit()

      num_correct =   input("num correct:")

      new_choices = []
      for choice in cc:
        sim_score = similarity(our_guess, choice)
        if sim_score == num_correct:
          new_choices.append(choice)

      cc = new_choices
      show_knockout(cc)

  elif args.solve:
    pass
  else:
    play_game()

main()
