<?php
namespace StarFluxx\Cards;

/*
 * Card: base class for all playable Card types
 */
abstract class Card extends \APP_GameClass
{
  public function __construct($cardId, $uniqueId)
  {
    $this->cardId = $cardId;
    $this->uniqueId = $uniqueId;
    $this->set = "base";
    $this->name = clienttranslate("Not Implemented");
    $this->subtitle = null;
    $this->description = null;
    $this->help = null;
  }

  /*
   * Attributes
   */
  protected $cardId;
  protected $uniqueId;

  /*
   * Getters
   */
  public function getCardId()
  {
    return $this->cardId;
  }
  public function getUniqueId()
  {
    return $this->uniqueId;
  }
  public function getCardSet()
  {
    return $this->set;
  }
  public function getName()
  {
    return $this->name;
  }
  public function getSubtitle()
  {
    return $this->subtitle;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function getHelp()
  {
    return $this->help;
  }

  /**
   * immediateEffectOnPlay : default function to execute when a Card is played from hand.
   * return: null if the game should continue the play loop,
   * or "state Transition Name" if another state need to be called
   */
  public function immediateEffectOnPlay($player_id)
  {
    return null;
  }

  /**
   * immediateEffectOnDiscard : default function to execute when card is removed from play.
   * return: null if the game should continue the play loop,
   * or "stateName" if another state need to be called
   */
  public function immediateEffectOnDiscard($player_id)
  {
    return null;
  }

  /**
   * resolvedBy : default function to execute when interactive decisions
   * for the played Rule card have been resolved (passed in via args).
   * return: null if the game should continue the play loop,
   * or "state Transition Name" if another state need to be called
   */
  public function resolvedBy($player_id, $args)
  {
    return null;
  }
}
