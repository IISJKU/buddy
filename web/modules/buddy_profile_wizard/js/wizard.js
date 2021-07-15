var config = {
  type: Phaser.AUTO,
  scale: {
    mode: Phaser.Scale.WIDTH_CONTROLS_HEIGHT,
    parent: 'phaser-container',
    width: 800,
    height: 600
  },
  physics: {
    default: 'matter',
    matter: {
      gravity: {
        y: 0.4
      },
      debug: false,
      debugBodyColor: 0xffffff
    }
  },

  backgroundColor: '#2d2d2d',
  scene: [MemoryGameShortTerm,Intro,ReadingGameTTSWord, MathGame, MemoryGame,ReadingGameText,ReadingGameTTSSentence ]

  /*scene: {
    preload: preload,
    create: create
  }*/
};


var game = new Phaser.Game(config);
