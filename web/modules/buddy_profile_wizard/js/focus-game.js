class FocusGame extends GameScene {

  constructor() {
    super('FocusGame');
    this.text = null;

    this.numberOfCups = 3;
    this.rowHeight = 150;
    this.cups = [];
    this.animatedCup1 = null;
    this.animatedCup2 = null;

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



    for(let i=0; i < this.numberOfCups; i++){

      let cupPosition = this.getPositionOfCub(i);

      let currentCup = this.add.sprite(cupPosition.x, cupPosition.y, 'cup');
      currentCup.setScale(0.4);
      this.cups.push(currentCup);

    }


    this.shuffleCups(0,2);
    /*

    this.follower = { t: 0, vec: new Phaser.Math.Vector2() };

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

    */



  }

  shuffleCups(cup1,cup2){


    let angle = Phaser.Math.Angle.Between(this.cups[cup1].x, this.cups[cup1].y,this.cups[cup2].x, this.cups[cup2].y);
    angle = Phaser.Math.RadToDeg(angle);

    let distance =  Phaser.Math.Distance.BetweenPoints(this.cups[cup1], this.cups[cup2])/2;
    console.log(distance);

    this.path1 = new Phaser.Curves.Path(this.cups[cup1].x, this.cups[cup1].y);
    this.path1.ellipseTo(distance, 100, 180, 360, true,angle);

    this.moveEnabled = true;

    this.cups[cup1].setData('vector', new Phaser.Math.Vector2());
    this.animatedCup1 = this.cups[cup1];

    let focusGame = this;
    this.tweens.add({
      targets: focusGame.cups[cup1],
      z: 1,
      ease: 'Sine.easeInOut',
      duration: 1000,
      repeat: -1,
      onComplete: function (tween, targets) {
        focusGame.moveEnabled = false;
      }
    });

    this.path2 = new Phaser.Curves.Path(this.cups[cup2].x, this.cups[cup2].y);
    this.path2.ellipseTo(distance, 100, 180, 360, true,angle+180);

    this.cups[cup2].setData('vector', new Phaser.Math.Vector2());
    this.animatedCup2 = this.cups[cup2];
    this.tweens.add({
      targets: focusGame.cups[cup2],
      z: 1,
      ease: 'Sine.easeInOut',
      duration: 1000,
      repeat: -1,
    });

  }

  getPositionOfCub(cupNumber){

    let layout = this.getLayout();

    let cupCount = 0;
    let rowIndex = 0;
    for(let i=0; i< layout.length; i++){

      if(cupNumber < cupCount+layout[i]){

        rowIndex = i;
        break;


      }
      cupCount += layout[i];

    }

    let numberOfCupInRow = cupNumber-cupCount;

    let xPos =0;
    if(layout[rowIndex]%2 === 0){
      let spaceBetweenItems  = this.cameras.main.width/(layout[rowIndex]+1);
      xPos = spaceBetweenItems*(numberOfCupInRow+1);
    }else{
      let spaceBetweenItems  = this.cameras.main.width/layout[rowIndex];
      xPos = spaceBetweenItems*numberOfCupInRow+spaceBetweenItems*0.5;

    }

    let yPos= Math.floor((rowIndex+1)/layout.length)* this.rowHeight+this.cameras.main.centerY;
    console.log(rowIndex,numberOfCupInRow);
    console.log(xPos,yPos);


    return {
      x: xPos,
      y: yPos,
    };

  }

  getLayout(){

    switch (this.numberOfCups) {

      case 3:
        return [2,1];
      case 4:
        return [2,2];
      case 5:
        return [3,2];
      default:
        return [3,2,1];

    }




  }

  startGame(){
    this.removeSound = this.sound.add('boing');



  }


  update ()
  {
   // this.graphics.clear();
   // this.graphics.lineStyle(2, 0xffffff, 1);

   // this.path.draw(this.graphics);


    if(this.moveEnabled){

      if(this.animatedCup1){
        let t = this.animatedCup1.z;
        let vec = this.animatedCup1.getData('vector');
        this.path1.getPoint(t, vec);

        this.animatedCup1.setPosition(vec.x, vec.y);

        this.animatedCup1.setDepth(this.animatedCup1.y);
      }

      if(this.animatedCup2){
        let t = this.animatedCup2.z;
        let vec = this.animatedCup2.getData('vector');
        this.path2.getPoint(t, vec);

        this.animatedCup2.setPosition(vec.x, vec.y);

        this.animatedCup2.setDepth(this.animatedCup2.y);
      }


      /*

      let t = this.cup.z;
      let vec = this.cup.getData('vector');

      //  The vector is updated in-place
      this.path.getPoint(t, vec);

      this.cup.setPosition(vec.x, vec.y);

      this.cup.setDepth(this.cup.y);

      */
    }

  }


}
