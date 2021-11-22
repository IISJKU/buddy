class FocusGame extends GameScene {

  constructor() {
    super('FocusGame');
    this.text = null;
    this.gamesWon = 0;
    this.gamesLost = 0;
    this.gamesPlayed = 0;
    this.gamesToPlay = 3;
    this.numberOfCups = 4;
    this.secondShufflePercentage = 30;
    this.numberOfShuffles = 10;
    this.shuffleDuration = 1000;
    this.rowHeight = 150;
    this.cups = [];
    this.distractions = [];
    this.distractionDelayTimer = 1000;
    this.currentDistractionCount = 0;
    this.maxNumbersOfDistractions = 4;
    this.maxDistractionAnimationTime = 5000;
    this.coin = null;
    this.animatedCups = [];
    this.animatedCup1 = null;
    this.animatedCup2 = null;
    this.cupFallHeight = 800;

    this.shuffleSteps = [];


  }

  preload() {

    super.preload();
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");

    this.load.image('cup', 'modules/buddy_profile_wizard/assets/img/focus_game/cup.png');
    this.load.image('coin', 'modules/buddy_profile_wizard/assets/img/focus_game/coin.png');
    this.load.spritesheet('mummy', 'modules/buddy_profile_wizard/assets/img/focus_game/mummy37x45.png', { frameWidth: 37, frameHeight: 45 });

    this.load.audio('focus_game_intro', soundFactory.getSound("focus_game","intro.mp3"));

    this.load.audio('zombie',  'modules/buddy_profile_wizard/assets/sounds/focus_game/zombie.wav');
    this.load.audio('wosh1',  'modules/buddy_profile_wizard/assets/sounds/focus_game/wosh1.mp3');
    this.load.audio('wosh2',  'modules/buddy_profile_wizard/assets/sounds/focus_game/wosh2.mp3');
    this.load.audio('cupImpact',  'modules/buddy_profile_wizard/assets/sounds/focus_game/cupImpact.mp3');

    this.load.audio('yes', 'modules/buddy_profile_wizard/assets/sounds/yes.wav');
    this.load.audio('no', 'modules/buddy_profile_wizard/assets/sounds/no.wav');

  }

  create() {

    this.wosh1 = this.sound.add('wosh1');
    this.wosh2 = this.sound.add('wosh1');
    this.cupImpact = this.sound.add('cupImpact');
    this.yes = this.sound.add('yes');
    this.no = this.sound.add('no');

    this.graphics = this.add.graphics();
    this.mummyAnimation = this.anims.create({
      key: 'walk',
      frames: this.anims.generateFrameNumbers('mummy'),
      frameRate: 16
    });





    this.showIntroScreen();





  }

  showIntroScreen(){
    this.createTitle(stringFactory.getString("focus_game_text_intro"));
    let focusGame = this;

    this.avatarButton = new AvatarAudioButton(this,"focus_game_intro",this.cameras.main.centerX, 180,function (){

    });
    this.avatarButton.init();
    this.add.existing(this.avatarButton);

    this.startButton = new IconButton(this, stringFactory.getString("math_game_start"), this.cameras.main.centerX, 300, "playIcon", function () {
      focusGame.titleText.destroy();
      focusGame.startButton.destroy();
      focusGame.avatarButton.destroy();
      focusGame.startGame();
    });
    this.startButton.init();
    this.add.existing(this.startButton);

  }

  startGame(){

    if(this.gamesPlayed < this.gamesToPlay){
      this.clear();
      this.setupCupsAndCoin();
      this.createShuffles();
      this.dropCups();
    }else{

      Director.changeScene("FocusGame",{
        "id": "FocusGame",
        "gamesWon": this.gamesWon,
        "gamesLost" : this.gamesLost
      });
    }


  }

  clear(){
    if(this.coin){
      this.coin.destroy();
    }

    for(let i=0; i < this.cups.length; i++){

      this.cups[i].destroy();
    }
  }

  setupCupsAndCoin(){
    this.cups = [];
    for(let i=0; i < this.numberOfCups; i++){

      let cupPosition = this.getPositionOfCub(i);

      let currentCup = this.add.sprite(cupPosition.x, cupPosition.y, 'cup');
      currentCup.setScale(0.4);
      this.cups.push(currentCup);

    }

    this.winningCup = this.cups[Math.floor(Math.random() * this.numberOfCups)];


    this.coin = this.add.sprite(this.winningCup.x, this.winningCup.y+this.cupFallHeight, 'coin');
    this.coin.setScale(0.4);
    this.coin.setDepth(-1);
  }

  createShuffles(){

    this.shuffleSteps = [];

    for(let i=0; i< this.numberOfShuffles; i++){




      let cup1 = Math.floor(Math.random() * this.numberOfCups);

      let chosenCups = [cup1];
      let cup2 = cup1;

      while(chosenCups.includes(cup2)  ){
        cup2 = Math.floor(Math.random() * this.numberOfCups);
      }

      chosenCups.push(cup2);

      let currentShuffle = [];
      currentShuffle.push([cup1,cup2]);

      if(this.numberOfCups > 3){

        let chance = Math.floor(Math.random() * 100);
        if(chance < this.secondShufflePercentage){

          let cup3 = cup1;

          while(chosenCups.includes(cup3)  ){
            cup3 = Math.floor(Math.random() * this.numberOfCups);
          }
          chosenCups.push(cup3);

          let cup4 = cup1;

          while(chosenCups.includes(cup4)  ){
            cup4 = Math.floor(Math.random() * this.numberOfCups);
          }


          currentShuffle.push([cup3,cup4]);



        }


      }

      this.shuffleSteps.push(currentShuffle);




    }

    console.log(this.shuffleSteps);
    this.totalTime = this.numberOfShuffles *this.shuffleDuration;

  }

  createDistraction(){

    this.currentDistractionCount++;
    let distractionType = 0;
    let direction = Math.floor(Math.random() *  2);

    if(distractionType === 0){
      let mummy = this.add.sprite(0, 300, 'mummy').setScale(2);
      mummy.play({ key: 'walk', repeat: -1 });

      let availableHeight = this.cameras.main.height-mummy.height;

      mummy.y = Math.floor(Math.random() * availableHeight);
      let mummyYPosition = Math.floor(Math.random() * availableHeight);

      let focusGame = this;
      if(direction === 0){
        this.tweens.add({
          targets: mummy,
          x: this.cameras.main.width+mummy.width,
          y: mummyYPosition,
          ease: 'Linear',
          duration: this.maxDistractionAnimationTime-Math.floor(Math.random() *  (this.maxDistractionAnimationTime/2)),
          onComplete: function (tween, targets){

            mummy.destroy();
            focusGame.currentDistractionCount--;
          }
        });
      }else{
        mummy.x = this.cameras.main.width+mummy.width;
        mummy.scaleX = -mummy.scaleX;
        this.tweens.add({
          targets: mummy,
          x: -mummy.width,
          y: mummyYPosition,
          ease: 'Linear',
          duration: this.maxDistractionAnimationTime-Math.floor(Math.random() * (this.maxDistractionAnimationTime/2)),
          onComplete: function (tween, targets){
            mummy.destroy();
            focusGame.currentDistractionCount--;
          }
        });
      }



      if(!this.zombieSound){
        this.zombieSound = this.sound.add('zombie');
      }
      this.zombieSound.play();
      this.distractions.push(mummy);
    }

  }

   dropCups(){

    for(let i=0; i < this.cups.length; i++){

      if(i === 0){
        let focusGame = this;
        this.tweens.add({
          targets: this.cups[i],
          props: {
            y: {value: this.cups[i].y+this.cupFallHeight, duration: 1000, ease: 'Bounce.easeOut'}
          },
          onComplete: function (tween, targets) {
            focusGame.coin.visible = false;
            focusGame.nextMove();
          }
        });
      }else{
        this.tweens.add({
          targets: this.cups[i],
          props: {
            y: {value: this.cups[i].y+this.cupFallHeight, duration: 1000, ease: 'Bounce.easeOut'}
          }
        });

      }





    }
     this.time.addEvent({
       delay: 200,                // ms
       callback: function (){
         this.cupImpact.play();
       },
       //args: [],
       callbackScope: this,
     });

  }

  nextMove(){

    this.animatedCups =  [];
    let nextMove = this.shuffleSteps.pop();
    if(nextMove){

      console.log("NEXT MOVE");
      for(let i=0; i < nextMove.length; i++){

        if(i !== 0){
          this.shuffleCups(nextMove[i][0],nextMove[i][1],false);
        }else{
          this.shuffleCups(nextMove[i][0],nextMove[i][1]);
        }
      }

    }else{
      this.coin.visible = true;
      this.coin.setPosition(this.winningCup.x, this.winningCup.y);

      for(let i=0; i < this.cups.length; i++){
        this.cups[i].setInteractive();
        let currentCup = this.cups[i];
        let winningCup = this.winningCup;
        let focusGame = this;
        this.cups[i].on('pointerdown', function (pointer) {

          if(currentCup === winningCup){
            this.gamesWon++;
            console.log("You win");
            console.log(focusGame.numberOfCups);
            if(focusGame.numberOfCups < 6){
              focusGame.numberOfCups++;

            }

            if(focusGame.maxNumbersOfDistractions < 14){
              focusGame.maxNumbersOfDistractions++;
            }

            if(focusGame.shuffleDuration > 400){
              focusGame.shuffleDuration = focusGame.shuffleDuration-100;
            }
            focusGame.yes.play();
          }else{
            this.gamesLost++;
            if(focusGame.numberOfCups > 2){
              focusGame.numberOfCups--;
            }
            if(focusGame.maxNumbersOfDistractions > 4){
              focusGame.maxNumbersOfDistractions--;
            }

            if(focusGame.shuffleDuration < 1000){
              focusGame.shuffleDuration = focusGame.shuffleDuration+100;
            }
            focusGame.tweens.add({
              targets: currentCup,
              props: {
                y: {value: currentCup.y-200, duration: 1000, ease: 'Bounce.easeOut'}
              }
            });


            focusGame.no.play();
          }

          focusGame.gamesPlayed++;
          focusGame.tweens.add({
            targets: winningCup,
            props: {
              y: {value: winningCup.y-200, duration: 1000, ease: 'Bounce.easeOut'}
            },
            onComplete: function (tween, targets) {

              focusGame.time.addEvent({
                delay: 2000,                // ms
                callback: function (){
                  focusGame.startGame();
                },
                callbackScope: this,
              });

            }
          });

          console.log(currentCup);

        });
      }
    }

  }

  shuffleCups(cup1,cup2,callback=true){

    console.log("shuffle:",cup1,cup2, callback);
    this.wosh1.play();



    let angle = Phaser.Math.Angle.Between(this.cups[cup1].x, this.cups[cup1].y,this.cups[cup2].x, this.cups[cup2].y);
    angle = Phaser.Math.RadToDeg(angle);

    let distance =  Phaser.Math.Distance.BetweenPoints(this.cups[cup1], this.cups[cup2])/2;
    let path1 = new Phaser.Curves.Path(this.cups[cup1].x, this.cups[cup1].y);
    path1.ellipseTo(distance, 100, 180, 360, true,angle);

    this.moveEnabled = true;

    this.cups[cup1].setData('vector', new Phaser.Math.Vector2());
    this.cups[cup1].setData('path', path1);
    this.animatedCup1 = this.cups[cup1];

    this.animatedCups.push(this.cups[cup1]);
    let focusGame = this;
    if(callback){
      this.tweens.add({
        targets: focusGame.cups[cup1],
        z: 1,
        ease: 'Sine.easeInOut',
        duration: focusGame.shuffleDuration,
        repeat: 0,
        onComplete: function (tween, targets) {

          //Waiting timer is needed. Otherwise last update would not be called with final position
          //This would lead to a missplacement of cups
          focusGame.waitingTimer = focusGame.time.addEvent({
            callback: focusGame.waitingTimerComplete,
            callbackScope: focusGame,
            delay: 10,
            loop: false
          });

        }
      });
    }else{
      this.tweens.add({
        targets: focusGame.cups[cup1],
        z: 1,
        ease: 'Sine.easeInOut',
        duration: focusGame.shuffleDuration,
        repeat: 0,
      });
    }


    let path2 = new Phaser.Curves.Path(this.cups[cup2].x, this.cups[cup2].y);
    path2.ellipseTo(distance, 100, 180, 360, true,angle+180);
    this.cups[cup2].setData('path', path2);
    this.cups[cup2].setData('vector', new Phaser.Math.Vector2());
    this.animatedCup2 = this.cups[cup2];
    this.animatedCups.push(this.cups[cup2]);
    this.tweens.add({
      targets: focusGame.cups[cup2],
      z: 1,
      ease: 'Sine.easeInOut',
      duration: focusGame.shuffleDuration,
      repeat: 0,
    });

  }
  waitingTimerComplete(){
    this.moveEnabled = false;
    this.nextMove();
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

    let yPos= Math.floor((rowIndex+1)/layout.length)* this.rowHeight+this.cameras.main.centerY-this.cupFallHeight;
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


  update (timestamp,delta)
  {

   // this.graphics.clear();
   // this.graphics.lineStyle(2, 0xffffff, 1);

   // this.path.draw(this.graphics);


    if(this.moveEnabled){

      this.totalTime-=delta;
      this.distractionDelayTimer-=delta;

      for(let i=0; i < this.animatedCups.length; i++){
        let t = this.animatedCups[i].z;
        let vec = this.animatedCups[i].getData('vector');
        let path = this.animatedCups[i].getData('path');
        path.getPoint(t, vec);

        this.animatedCups[i].setPosition(vec.x, vec.y);

        this.animatedCups[i].setDepth(this.animatedCup1.y);

      }
      /*
      if(this.animatedCup1){
        let t = this.animatedCup1.z;
        let vec = this.animatedCup1.getData('vector');
        let path = this.animatedCup1.getData('path');
        path.getPoint(t, vec);

        this.animatedCup1.setPosition(vec.x, vec.y);

        this.animatedCup1.setDepth(this.animatedCup1.y);
      }

      if(this.animatedCup2){
        let t = this.animatedCup2.z;
        let vec = this.animatedCup2.getData('vector');
        let path = this.animatedCup2.getData('path');
        path.getPoint(t, vec);

        this.animatedCup2.setPosition(vec.x, vec.y);

        this.animatedCup2.setDepth(this.animatedCup2.y);
      }

       */



      if(this.distractionDelayTimer < 0){
        if(this.currentDistractionCount < this.maxNumbersOfDistractions && this.totalTime > this.maxDistractionAnimationTime){


          this.createDistraction();
          this.distractionDelayTimer = Math.floor(Math.random() * 3000);
        }
      }

    }

  }


}
