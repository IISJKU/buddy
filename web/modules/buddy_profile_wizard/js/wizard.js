var config = {
  type: Phaser.AUTO,
  scale: {
    mode: Phaser.Scale.WIDTH_CONTROLS_HEIGHT,
    parent: 'phaser-container',
    width: 800,
    height: 600
  },
  physics: {
    default: 'arcade',
    arcade: {
      gravity: { y: 200 }
    }
  },
  backgroundColor: '#2d2d2d',
  scene: [ReadingGame, Intro, MathGame, MemoryGame ]

  /*scene: {
    preload: preload,
    create: create
  }*/
};


var game = new Phaser.Game(config);
