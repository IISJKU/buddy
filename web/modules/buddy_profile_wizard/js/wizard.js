var config = {
  type: Phaser.AUTO,
  scale: {
    mode: Phaser.Scale.WIDTH_CONTROLS_HEIGHT,
  //  parent: 'phaser-container',
    width: 800,
    height: 600
  },
  parent: 'phaser-container',
  dom: {
    createContainer: true
  },
  physics: {
    default: 'matter',
    matter: {
      gravity: {
        y: 0.4
      },
      debug: false,
      debugBodyColor: 0xffffff
    },
  },

  backgroundColor: '#2d2d2d',
  scene: [WritingGame,Intro, ReadingGameTTSWord, MathGame, MemoryGame, ReadingGameText, ReadingGameTTSSentence, MemoryGameShortTerm, FocusGame]

  /*scene: {
    preload: preload,
    create: create
  }*/
};


var game = new Phaser.Game(config);
