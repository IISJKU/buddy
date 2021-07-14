class MemoryGameShortTerm extends GameScene {

  constructor() {
    super('MemoryGameShortTerm');
    this.text = null;
    this.delay = 1000;
    this.personScale = 0.55;
    this.personWidth = 100;
    this.steps = [];
    this.conveyor_belt = null;
    this.actualThing = null;
  }

  preload() {
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");
    this.load.image('conveyor-belt', 'modules/buddy_profile_wizard/assets/img/memory_game_short_term/conveyor-belt.png');
    this.load.image('girl', 'modules/buddy_profile_wizard/assets/img/girl.png');
    this.load.image('boy', 'modules/buddy_profile_wizard/assets/img/boy.png');

  }

  create() {


    this.conveyor_belt =  this.matter.add.image(500, 400, 'conveyor-belt', null, { isStatic: true });
    this.conveyor_belt.setScale(0.4);
    this.conveyor_belt.setCollisionGroup(1);
    this.conveyor_belt.setCollidesWith(1);

    this.sprite1 = this.matter.add.image(600, 100, 'girl');
    this.sprite1.setFriction(0);
    this.sprite1.setCollisionGroup(1);
    this.sprite1.setCollidesWith(1);
    let memoryGameShort = this;
    this.matter.world.on('collisionstart', function (event) {


      if(event.pairs[0].bodyA.gameObject === memoryGameShort.conveyor_belt){


        memoryGameShort.sprite1.setVelocity(-5, 0);
      }else if(event.pairs[0].bodyB.gameObject === memoryGameShort.conveyor_belt){
        memoryGameShort.sprite1.setVelocity(-5, 0);
      }else{

        memoryGameShort.sprite1.setVelocity(0, 0);
      }

      console.log("aa");

    });


    const body4 = this.matter.add.rectangle(100, this.cameras.main.height-100,200 ,200,
      {
        isSensor: true,
        label: 'suitcaseSensor',
        isStatic: true
      }
      );

    this.startButton = new IconButton(this,stringFactory.getString("math_game_start"),this.cameras.main.centerX, 300,"playIcon",function (){


      memoryGameShort.sprite1.setVelocity(0, -5);
      memoryGameShort.sprite1.setCollisionGroup(0)
      memoryGameShort.sprite1.setCollidesWith(0)

    });
    this.startButton.init();
    this.add.existing(this.startButton);

  }

  update(){


  }


}
