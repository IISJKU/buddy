var config = {
  type: Phaser.AUTO,
  scale: {
    mode: Phaser.Scale.NONE,

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
  scene: [Intro,UnderstandingQuiz,ReadingGameTTSSentence, FocusGame,WritingGame,TimeManagementQuiz, ReadingGameTTSWord, MathGame, MemoryGame, ReadingGameText, MemoryGameShortTerm]

  /*scene: {
    preload: preload,
    create: create
  }*/
};


var game = new Phaser.Game(config);
