class FocusGame extends GameScene {

  constructor() {
    super('FocusGame');
    this.text = null;

  }

  preload() {

    super.preload();
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");

    this.load.image('cup', 'modules/buddy_profile_wizard/assets/img/focus_game/cup.png');
    this.load.image('coin', 'modules/buddy_profile_wizard/assets/img/focus_game/coin.png');



  }

  create() {

    this.createTitle(stringFactory.getString("memory_game_short_term_title"));
    let memoryGameShortTerm = this;

    this.graphics = this.add.graphics();

    this.follower = { t: 0, vec: new Phaser.Math.Vector2() };
    this.add.sprite(50, 50, 'cup');

    this.path = new Phaser.Curves.Path(500, 500);


    this.path.ellipseTo(150, 100, 0, 180, true,30);

    this.cup = this.add.sprite(50, 50, 'cup');
    this.cup.setScale(0.4);

    this.cup.setData('vector', new Phaser.Math.Vector2());

    this.moveEnabled = true;
    let focusGame = this;
    this.tweens.add({
      targets: focusGame.cup,
      z: 1,
      ease: 'Sine.easeInOut',
      duration: 1000,
      repeat: 0,
      onComplete: function (tween, targets) {
        focusGame.moveEnabled = false;
      }
    });


    /*
    this.avatarButton = new AvatarAudioButton(this,"memory_game_short_term_intro",this.cameras.main.centerX, 180,function (){

    });
    this.avatarButton.init();
    this.add.existing(this.avatarButton);

    this.startButton = new IconButton(this, stringFactory.getString("math_game_start"), this.cameras.main.centerX, 300, "playIcon", function () {
      memoryGameShortTerm.titleText.destroy();
      memoryGameShortTerm.startButton.destroy();
      memoryGameShortTerm.avatarButton.destroy();
      memoryGameShortTerm.startGame();
    });
    this.startButton.init();
    this.add.existing(this.startButton);
    */


  }
  startGame(){
    this.removeSound = this.sound.add('boing');



  }


  update ()
  {
    this.graphics.clear();
    this.graphics.lineStyle(2, 0xffffff, 1);

    this.path.draw(this.graphics);


    if(this.moveEnabled){
      let t = this.cup.z;
      let vec = this.cup.getData('vector');

      //  The vector is updated in-place
      this.path.getPoint(t, vec);

      this.cup.setPosition(vec.x, vec.y);

      this.cup.setDepth(this.cup.y);
    }

  }


}
