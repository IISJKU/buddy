class MemoryGameShortTerm extends GameScene {

  constructor() {
    super('MemoryGameShortTerm');
    this.text = null;
    this.delay = 1000;
    this.personScale = 0.55;
    this.personWidth = 100;
    this.steps = [];
    this.conveyor_belt = null;
    this.actualItem = null;
    this.actualItemFallingFromTop = true;
    this.items = [];
    this.testItems = [];
    this.itemsInSuitcase = [];
    this.numberOfDuplicates = 10;
    this.overallItems = 10;
    this.currentItemIndex = 0;
    this.errorsRemoved = 0;
    this.errorsDuplicate = 0;
  }

  preload() {
    super.preload();
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");
    this.load.image('conveyor-belt', 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/conveyor-belt.png');
    this.load.image('suitcase', 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/suitcase.png');
    this.load.image('suitcase_front', 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/suitcase_front.png');



    for(let i=0; i < this.overallItems; i++){
      let itemString = "item"+(i+1).toString();
      this.items.push(itemString);
      this.load.image(itemString, 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/'+itemString+'.png');

    }


    this.load.audio('memory_game_short_term_intro', soundFactory.getSound("memory_game_short_term","short_term_intro.mp3"));
    this.load.audio('boing', 'modules/buddy_profile_wizard/assets/sounds/boing.wav');
    this.load.audio('impact', 'modules/buddy_profile_wizard/assets/sounds/soft_impact1.wav');
    this.load.audio('suitcase', 'modules/buddy_profile_wizard/assets/sounds/suitcase_sucks_in_item.wav');




    this.load.image('girl', 'modules/buddy_profile_wizard/assets/img/girl.png');
    this.load.image('boy', 'modules/buddy_profile_wizard/assets/img/boy.png');



  }

  create() {

    this.createTitle(stringFactory.getString("memory_game_short_term_title"));
    let memoryGameShortTerm = this;

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

  }
  startGame(){
    this.removeSound = this.sound.add('boing');
    this.impactSound = this.sound.add('impact');
    this.suitcaseSound = this.sound.add('suitcase');
    this.suitcase = this.add.sprite(140, this.cameras.main.height - 105, "suitcase");
    this.suitcase.setDepth(1);
    this.conveyor_belt = this.matter.add.image(500, 400, 'conveyor-belt', null, {isStatic: true});
    this.conveyor_belt.setScale(0.4);
    this.conveyor_belt.setDepth(3);
    this.conveyor_belt.setCollisionGroup(1);
    this.conveyor_belt.setCollidesWith(1);


    this.testItems = gameUtil.shuffle(this.items);

    for(let i=0; i < this.numberOfDuplicates; i++){

      let duplicateItem = this.items[Math.floor(Math.random()*this.items.length)];
      console.log("Dub:"+duplicateItem);
      this.testItems.push(duplicateItem);
    }

    this.testItems = gameUtil.shuffle(this.items);



    let memoryGameShort = this;
    this.matter.world.on('collisionstart', function (event) {


      if (event.pairs[0].bodyA.gameObject === memoryGameShort.conveyor_belt) {

        memoryGameShort.impactSound.play();
        memoryGameShort.actualItemFallingFromTop = false;
        memoryGameShort.actualItem.setVelocity(-4, 0);
      } else if (event.pairs[0].bodyB.gameObject === memoryGameShort.conveyor_belt) {
        memoryGameShort.actualItem.setVelocity(-4, 0);
        memoryGameShort.actualItemFallingFromTop = false;
        memoryGameShort.impactSound.play();
      } else {

        if (event.pairs[0].bodyA === memoryGameShort.fallSensor || event.pairs[0].bodyB === memoryGameShort.fallSensor) {
          memoryGameShort.inputDisabled = true;

        } else if (event.pairs[0].bodyA === memoryGameShort.slowSensor || event.pairs[0].bodyB === memoryGameShort.slowSensor) {
          memoryGameShort.suitcaseSound.play();
          memoryGameShort.actualItem.setVelocity(0, 0);
          memoryGameShort.itemInSuitcase = true;
        } else if (event.pairs[0].bodyA === memoryGameShort.destroySensor || event.pairs[0].bodyB === memoryGameShort.destroySensor) {

          memoryGameShort.nextStep();
        }

      }


    });

    this.fallSensor = this.matter.add.rectangle(130, this.conveyor_belt.y, 300, 20,
      {
        isSensor: true,
        label: 'suitcaseSensor',
        isStatic: true
      }
    );


    this.slowSensor = this.matter.add.rectangle(130, this.cameras.main.height - 20, 200, 20,
      {
        isSensor: true,
        label: 'suitcaseSensor',
        isStatic: true
      }
    );

    this.destroySensor = this.matter.add.rectangle(130, this.cameras.main.height + 250, 5000, 20,
      {
        isSensor: true,
        label: 'suitcaseSensor',
        isStatic: true
      }
    );


    this.startButton = new IconButton(this, stringFactory.getString("memory_game_short_term_remove_item"), this.cameras.main.width - 150, this.cameras.main.height - 100, "noIcon", function () {

      if(!memoryGameShort.inputDisabled){

        memoryGameShort.removeCurrentObject();
      }

    });
    this.startButton.init();
    this.add.existing(this.startButton);

    this.suitcaseFront = this.add.sprite(140, this.cameras.main.height - 105, "suitcase_front");
    this.suitcaseFront.setDepth(3);

    this.ground1 = this.matter.add.rectangle(300, this.cameras.main.height - 100, 150, 10, {
      isStatic: true,
      angle: -60 * Math.PI / 180,
      friction: 0,

    });


    this.nextStep();

  }

  nextStep() {

    if(this.actualItemAssetName){
      //Add error because this was already in the bag
      if(this.itemsInSuitcase.includes(this.actualItemAssetName)){

        this.errorsDuplicate++;
        console.log("Muh");
      }else{
        this.itemsInSuitcase.push(this.actualItemAssetName);
      }
    }


    if(this.currentItemIndex < this.testItems.length){

      this.spawnObject(this.testItems[this.currentItemIndex]);
      this.currentItemIndex++;
    }else{


      let percent = ((this.testItems.length-this.errorsRemoved-this.errorsDuplicate)/this.testItems.length)*100;

      Director.changeScene("MemoryGameShortTerm",{
        "id": "Memory",
        "result": percent,
      });
      /*
      this.createTitle("Reached percent:"+percent);
      console.log("Errors:"+this.errors);
      console.log(this.errors+"/"+this.testItems.length);

       */

    }

  }

  removeCurrentObject() {
    if (this.actualItem) {

      this.removeSound.play();
      //Add error because this was not in the bag
      if(!this.itemsInSuitcase.includes(this.actualItemAssetName)){

        this.errorsRemoved++;

      }


      this.inputDisabled = true;

      if (this.actualItemFallingFromTop) {

        this.actualItem.setVelocity(-10, -6);
        this.actualItem.setAngularVelocity(0.2);

        let memoryGameShort = this;
        this.currentTimeout = setTimeout(function () {
          memoryGameShort.nextStep();
        }, 2000);
      }else if (this.actualItemFallingInSuitcase) {

        this.actualItem.setVelocity(-10, -8);
        this.actualItem.setAngularVelocity(0.2);
        let memoryGameShort = this;
        this.currentTimeout = setTimeout(function () {
          memoryGameShort.nextStep();
        }, 1000);
      } else {

        this.actualItem.setVelocity(0, -5);
        this.actualItem.setAngularVelocity(0.2);
        let memoryGameShort = this;
        this.currentTimeout = setTimeout(function () {
          memoryGameShort.nextStep();
        }, 2500);

      }
      this.actualItem.setCollisionGroup(0);
      this.actualItem.setCollidesWith(0);

      //Reset this, otherwise it would also count as error in next step method;
      this.actualItemAssetName = null;

    }


  }

  spawnObject(assetName) {

    if (this.actualItem) {
      this.actualItem.destroy();
      this.actualItem = null;
      this.inputDisabled = false;
      this.actualItemFallingFromTop = true;
      this.itemInSuitcase = false;
    }

    this.actualItemAssetName = assetName;
    this.actualItem = this.matter.add.image(600, -100, assetName);
    this.actualItem.setFriction(0);
    this.actualItem.setDepth(2);
    this.actualItem.setCollisionGroup(1);
    this.actualItem.setCollidesWith(1);
  }

  update() {


  }


}
