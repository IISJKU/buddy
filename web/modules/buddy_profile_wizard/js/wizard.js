var config = {
  type: Phaser.AUTO,
  scale: {
    _mode: Phaser.Scale.WIDTH_CONTROLS_HEIGHT,
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
  scene: [Intro, MathGame, MemoryGame ]

  /*scene: {
    preload: preload,
    create: create
  }*/
};

var game = new Phaser.Game(config);
