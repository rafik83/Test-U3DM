<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Address;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\Color;
use AppBundle\Entity\Embeddable\Dimensions;
use AppBundle\Entity\Finishing;
use AppBundle\Entity\Layer;
use AppBundle\Entity\Maker;
use AppBundle\Entity\Material;
use AppBundle\Entity\Printer;
use AppBundle\Entity\PrinterProduct;
use AppBundle\Entity\PrinterProductFinishing;
use AppBundle\Entity\Tag;
use AppBundle\Entity\Technology;
use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoadData implements ORMFixtureInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // Administrators
        $password = '3dgator';
        $logins = array(
            'jb@alabaz.com' => array(
                'firstname' => 'JB',
                'lastname'  => 'Alabaz'
            ),
            'alex@alabaz.com' => array(
                'firstname' => 'Alex',
                'lastname'  => 'Alabaz'
            ),
            'michael.introligator@thegatorprojects.fr' => array(
                'firstname' => 'Michael',
                'lastname'  => 'Introligator'
            ),
            'ludovic.gourdon@thegatorprojects.fr' => array(
                'firstname' => 'Ludovic',
                'lastname'  => 'Gourdon'
            )
        );
        foreach ($logins as $email => $data) {
            $admin = new Administrator();
            $admin->setEmail($email);
            $admin->setFirstname($data['firstname']);
            $admin->setLastname($data['lastname']);
            $admin->setPassword($this->encoder->encodePassword($admin, $password));
            $manager->persist($admin);
        }

        // Ref Technology data
        $technoFdm = new Technology();
        $technoFdm->setName('FDM')->setFillingRate(true);
        $manager->persist($technoFdm);
        $technoSla = new Technology();
        $technoSla->setName('SLA')->setFillingRate(false);
        $manager->persist($technoSla);

        // Ref Material data
        $materialPla = new Material();
        $materialPla->setName('PLA')->addTechnology($technoFdm);
        $manager->persist($materialPla);
        $materialResine1 = new Material();
        $materialResine1->setName('Résine 1')->addTechnology($technoSla);
        $manager->persist($materialResine1);
        $materialResine2 = new Material();
        $materialResine2->setName('Résine 2')->addTechnology($technoSla);
        $manager->persist($materialResine2);

        // Ref Color data
        $colorRed = new Color();
        $colorRed->setName('Rouge')->setHexadecimalCode('#FF0000');
        $manager->persist($colorRed);
        $colorGreen = new Color();
        $colorGreen->setName('Vert')->setHexadecimalCode('#00FF00');
        $manager->persist($colorGreen);
        $colorBlue = new Color();
        $colorBlue->setName('Bleu')->setHexadecimalCode('#0000FF');
        $manager->persist($colorBlue);

        // Ref Layer data
        $layer100 = new Layer();
        $layer100->setHeight(100);
        $manager->persist($layer100);
        $layer150 = new Layer();
        $layer150->setHeight(150);
        $manager->persist($layer150);
        $layer200 = new Layer();
        $layer200->setHeight(200);
        $manager->persist($layer200);

        // Ref Finishing data
        $finishingPolissage = new Finishing();
        $finishingPolissage->setName('Polissage');
        $manager->persist($finishingPolissage);
        $finishingVernis = new Finishing();
        $finishingVernis->setName('Vernis');
        $manager->persist($finishingVernis);

        // User accounts and related Makers
        $address = new Address();
        $address->setLastname('Alabaz')->setFirstname('Test')->setCompany('Alabaz');
        $address->setStreet1('10 rue de Penthièvre')->setZipcode('75008')->setCity('Paris')->setCountry('FR')->setTelephone('0100000000');
        $pickupAddress = clone $address;
        $userPassword = 'azertyui';
        $user1 = new User();
        $user1->setEmail('jb@alabaz.com')->setLastname('Alabaz')->setFirstname('JB')->setType(User::TYPE_INDIVIDUAL)->setEnabled(true)->setNewsletter(false);
        $user1->setPassword($this->encoder->encodePassword($user1, $userPassword));
        $maker1 = new Maker();
        $maker1->setLastname('Alabaz')->setFirstname('JB')->setCompany('Acme')->setCompanyType('SAS')->setSiren('12345678')->setAddress($address)->setEnabled(true)->setAvailable(true);
        $maker1->setPrinter(true)->setUser($user1);
        $maker1->setPickup(true)->setPickupAddress($pickupAddress);
        $manager->persist($user1);
        $manager->persist($maker1);
        $user2 = new User();
        $user2->setEmail('alex@alabaz.com')->setLastname('Alabaz')->setFirstname('Alex')->setCompany('Alabaz')->setType(User::TYPE_COMPANY)->setEnabled(true)->setNewsletter(false);
        $user2->setPassword($this->encoder->encodePassword($user2, $userPassword));
        $maker2 = new Maker();
        $maker2->setLastname('Alabaz')->setFirstname('Alex')->setCompany('Alabaz')->setEnabled(true)->setAvailable(true);
        $maker2->setPrinter(true)->setUser($user2);
        $maker2->setPickup(false);
        $manager->persist($user2);
        $manager->persist($maker2);

        // Printers
        $maxDimensions = new Dimensions(500, 500, 500);
        $printer1 = new Printer();
        $printer1
            ->setModel('Printer FDM 1')
            ->setMaker($maker1)
            ->setAvailable(true)
            ->setMinVolume(1000)
            ->setMaxDimensions($maxDimensions)
            ->setTechnology($technoFdm)
            ->setSetupPrice(500);
        $printer2 = new Printer();
        $printer2
            ->setModel('Printer FDM 2')
            ->setMaker($maker2)
            ->setAvailable(true)
            ->setMinVolume(500)
            ->setMaxDimensions($maxDimensions)
            ->setTechnology($technoFdm)
            ->setSetupPrice(1000);
        $printer3 = new Printer();
        $printer3
            ->setModel('Printer SLA')
            ->setMaker($maker2)
            ->setAvailable(true)
            ->setMinVolume(1000)
            ->setMaxDimensions($maxDimensions)
            ->setTechnology($technoSla)
            ->setSetupPrice(100);

        // Printer products for Printer FDM 1
        $product1_1 = new PrinterProduct();
        $product1_1
            ->setAvailable(true)
            ->setLayer($layer100)
            ->setMaterial($materialPla)
            ->addColor($colorRed)->addColor($colorGreen)
            ->setPrice25(25)
            ->setPrice50(50)
            ->setPrice100(100);
        $finishing1_1_1 = new PrinterProductFinishing();
        $finishing1_1_1->setFinishing($finishingPolissage)->setPrice(20);
        $finishing1_1_2 = new PrinterProductFinishing();
        $finishing1_1_2->setFinishing($finishingVernis)->setPrice(30);
        $product1_1->addFinishing($finishing1_1_1)->addFinishing($finishing1_1_2);

        $product1_2 = new PrinterProduct();
        $product1_2
            ->setAvailable(true)
            ->setLayer($layer150)
            ->setMaterial($materialPla)
            ->addColor($colorRed)->addColor($colorGreen)->addColor($colorBlue)
            ->setPrice25(35)
            ->setPrice50(70)
            ->setPrice100(130);
        $finishing1_2_1 = new PrinterProductFinishing();
        $finishing1_2_1->setFinishing($finishingPolissage)->setPrice(40);
        $finishing1_2_2 = new PrinterProductFinishing();
        $finishing1_2_2->setFinishing($finishingVernis)->setPrice(60);
        $product1_2->addFinishing($finishing1_2_1)->addFinishing($finishing1_2_2);

        $printer1->addProduct($product1_1)->addProduct($product1_2);

        // Printer products for Printer FDM 2
        $product2_1 = new PrinterProduct();
        $product2_1
            ->setAvailable(true)
            ->setLayer($layer100)
            ->setMaterial($materialPla)
            ->addColor($colorRed)->addColor($colorGreen)
            ->setPrice25(15)
            ->setPrice50(30)
            ->setPrice100(80);
        $product2_2 = new PrinterProduct();
        $product2_2
            ->setAvailable(true)
            ->setLayer($layer150)
            ->setMaterial($materialPla)
            ->addColor($colorRed)->addColor($colorGreen)
            ->setPrice25(30)
            ->setPrice50(60)
            ->setPrice100(120);
        $product2_3 = new PrinterProduct();
        $product2_3
            ->setAvailable(true)
            ->setLayer($layer200)
            ->setMaterial($materialPla)
            ->addColor($colorRed)
            ->setPrice25(60)
            ->setPrice50(100)
            ->setPrice100(200);
        $printer2->addProduct($product2_1)->addProduct($product2_2)->addProduct($product2_3);

        // Printer products for Printer SLA
        $product3_1 = new PrinterProduct();
        $product3_1
            ->setAvailable(true)
            ->setLayer($layer100)
            ->setMaterial($materialResine1)
            ->addColor($colorRed)->addColor($colorGreen)
            ->setPrice100(10);
        $product3_2 = new PrinterProduct();
        $product3_2
            ->setAvailable(true)
            ->setLayer($layer100)
            ->setMaterial($materialResine2)
            ->addColor($colorRed)->addColor($colorGreen)
            ->setPrice100(20);
        $printer3->addProduct($product3_1)->addProduct($product3_2);

        // persist printers (cascading will persist products)
        $manager->persist($printer1);
        $manager->persist($printer2);
        $manager->persist($printer3);

        // Domain Tags
        $domains = array(
            'Architecture',
            'Art',
            'Automobile',
            'BTP',
            'Cinéma',
            'Conseil',
            'Designer',
            'Education',
            'Formation',
            'Imprimeur',
            'Ingénierie',
            'Jeux',
            'Luxe',
            'Maquettiste',
            'Marketing',
            'Médical',
            'Nautique',
            'Prototypage'
        );
        foreach ($domains as $name) {
            $tag = new Tag();
            $tag->setType(Tag::TYPE_DOMAIN);
            $tag->setName($name);
            $tag->setEnabled(true);
            $manager->persist($tag);
        }

        // Technology Tags
        $technologies = array(
            'Logiciel: 123Design',
            'Logiciel: 3dsMAX',
            'Logiciel: Autocad',
            'Logiciel: Blender',
            'Logiciel: Cinéma4D',
            'Logiciel: FreeCAD',
            'Logiciel: Leopoly',
            'Logiciel: Maya',
            'Logiciel: Rhino3d',
            'Logiciel: Sketchup Make',
            'Logiciel: Solidworks',
            'Logiciel: TrinkerCAD',
            'Process: Dépôt de matière sous flux d’énergie (DED)',
            'Process: Digital Light Processing (DLP)',
            'Process: Direct Metal Laser Sintering (DMLS)',
            'Process: DLP en Mouvement (Moving Light)',
            'Process: Dépôt de matière fondue (FDM)',
            'Process: Film Transfert Imaging (FTI)',
            'Process: Frittage de poudre par laser (SLM)',
            'Process: Frittage Sélectif Laser (SLS)',
            'Process: Fusion de faisceau d’électron (EBM)',
            'Process: Liage de poudre',
            'Process: PolyJet',
            'Process: Projection de matière multiple (MJM)',
            'Process: Stéréolithographie (SLA)',
            'Scanner: Lumière structurée',
            'Scanner: Lumière modulée',
            'Scanner: Manuel',
            'Scanner: Par contact',
            'Scanner: Par décalage de phase',
            'Scanner: Par holographie conoscopique',
            'Scanner: Par photogramétrie',
            'Scanner: Par temps de vol',
            'Scanner: Sans contact passif',
            'Scanners: Silhouette',
            'Scanners: Stéréoscopiques'
        );
        foreach ($technologies as $name) {
            $tag = new Tag();
            $tag->setType(Tag::TYPE_TECHNOLOGY);
            $tag->setName($name);
            $tag->setEnabled(true);
            $manager->persist($tag);
        }

        $manager->flush();
    }
}