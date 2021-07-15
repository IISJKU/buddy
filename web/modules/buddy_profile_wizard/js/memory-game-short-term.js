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
    this.actualItemFallingInSuitcase = false;
    this.actualItemFallingFromTop = true;
    this.items = [];
    this.testItems = [];
    this.consumedTestItems = [];
    this.numberOfDuplicates = 10;
    this.currentItemIndex = 0;
  }

  preload() {
    super.preload();
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");
    this.load.image('conveyor-belt', 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/conveyor-belt.png');
    this.load.image('suitcase', 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/suitcase.png');
    this.load.image('suitcase_front', 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/suitcase_front.png');



    for(let i=1; i < 18; i++){
      let itemString = "item"+(i+1).toString();
      this.items.push(itemString);
      this.load.image(itemString, 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/'+itemString+'.png');

    }






    this.load.image('girl', 'modules/buddy_profile_wizard/assets/img/girl.png');
    this.load.image('boy', 'modules/buddy_profile_wizard/assets/img/boy.png');



  }

  create() {

    this.suitcase = this.add.sprite(140, this.cameras.main.height - 105, "suitcase");
    this.conveyor_belt = this.matter.add.image(500, 400, 'conveyor-belt', null, {isStatic: true});
    this.conveyor_belt.setScale(0.4);
    this.conveyor_belt.setCollisionGroup(1);
    this.conveyor_belt.setCollidesWith(1);


    this.testItems = gameUtil.shuffle(this.items);

    for(let i=0; i < this.numberOfDuplicates; i++){

      let duplicateItem = this.items[Math.floor(Math.random()*this.items.length)];
      this.testItems.push(duplicateItem);
    }

    this.testItems = gameUtil.shuffle(this.items);



    let memoryGameShort = this;
    this.matter.world.on('collisionstart', function (event) {


      if (event.pairs[0].bodyA.gameObject === memoryGameShort.conveyor_belt) {


        memoryGameShort.actualItemFallingFromTop = false;
        memoryGameShort.actualItem.setVelocity(-4, 0);
      } else if (event.pairs[0].bodyB.gameObject === memoryGameShort.conveyor_belt) {
        memoryGameShort.actualItem.setVelocity(-4, 0);
        memoryGameShort.actualItemFallingFromTop = false;
      } else {

        if (event.pairs[0].bodyA === memoryGameShort.fallSensor || event.pairs[0].bodyB === memoryGameShort.fallSensor) {
          memoryGameShort.actualItemFallingInSuitcase = true;
        } else if (event.pairs[0].bodyA === memoryGameShort.slowSensor || event.pairs[0].bodyB === memoryGameShort.slowSensor) {
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


    this.startButton = new IconButton(this, stringFactory.getString("memory_game_short_term_remove_item"), this.cameras.main.centerX, 300, "noIcon", function () {

      if(!memoryGameShort.itemInSuitcase){

        memoryGameShort.removeCurrentObject();
      }

    });
    this.startButton.init();
    this.add.existing(this.startButton);

    this.suitcase = this.add.sprite(140, this.cameras.main.height - 105, "suitcase_front");

    this.ground1 = this.matter.add.rectangle(300, this.cameras.main.height - 100, 150, 10, {
      isStatic: true,
      angle: -60 * Math.PI / 180,
      friction: 0,

    });


    this.nextStep();

  }

  nextStep() {

    if(this.currentItemIndex < this.testItems.length){

      this.spawnObject(this.testItems[this.currentItemIndex]);
      this.currentItemIndex++;
    }else{
      console.log("test finished");
    }

  }

  removeCurrentObject() {
    if (this.actualItem) {
      this.inputDisabled = true;
      if (this.actualItemFallingFromTop) {

        this.actualItem.setVelocity(-10, -4);
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

    }


  }

  spawnObject(assetName) {

    if (this.actualItem) {
      this.actualItem.destroy();
      this.actualItem = null;
      this.actualItemFallingInSuitcase = false;
      this.actualItemFallingFromTop = true;
      this.itemInSuitcase = false;
    }

    this.actualItem = this.matter.add.image(600, -100, assetName);
    this.actualItem.setFriction(0);
    this.actualItem.setCollisionGroup(1);
    this.actualItem.setCollidesWith(1);
  }

  update() {


  }


}
